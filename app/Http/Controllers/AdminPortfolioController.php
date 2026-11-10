<?php

namespace App\Http\Controllers;

use App\Models\PortfolioProject;
use App\Support\PortfolioCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPortfolioController extends Controller
{
    public function index(PortfolioCatalog $catalog): View
    {
        $projects = $catalog->all(true);

        return view('admin.portfolio.index', compact('projects'));
    }

    public function create(): View
    {
        return view('admin.portfolio.form', [
            'project' => [
                'admin_key' => null,
                'legacy_source_id' => null,
                'title' => '',
                'slug' => '',
                'vehicle_make' => '',
                'vehicle_model' => '',
                'vehicle_year' => '',
                'work_order' => '',
                'upgrade_type' => '',
                'project_date' => '',
                'service_area' => '',
                'client_name' => '',
                'description' => '',
                'image_paths' => [],
                'images' => [],
                'video_url' => null,
                'is_published' => true,
                'is_legacy' => false,
            ],
            'editing' => false,
        ]);
    }

    public function store(Request $request, PortfolioCatalog $catalog): RedirectResponse
    {
        $data = $this->validatedData($request);
        $this->ensureSlugIsUnique($data['slug'], null, $catalog);
        $project = new PortfolioProject;
        $project->fill($this->projectFields($data));
        $project->images = $this->uploadedImages($request, [], $data['slug']);
        $project->video_path = $request->hasFile('video')
            ? $this->storeUpload($request->file('video'), 'videos/'.$data['slug'])
            : null;
        $project->updated_by = $request->user()->id;
        $project->save();

        $this->recordActivity($request, 'portfolio.created', $project, 'Added portfolio project '.$project->title.'.');

        return redirect()->route('admin.portfolio')->with('success', 'Portfolio project created.');
    }

    public function edit(string $project, PortfolioCatalog $catalog): View
    {
        $record = $catalog->findForAdmin($project);
        abort_unless($record, 404);

        return view('admin.portfolio.form', ['project' => $record, 'editing' => true]);
    }

    public function update(Request $request, string $project, PortfolioCatalog $catalog): RedirectResponse
    {
        $record = $catalog->findForAdmin($project);
        abort_unless($record, 404);

        $existing = $this->findOrNewProject($project, $record);
        $data = $this->validatedData($request, $existing);
        $this->ensureSlugIsUnique($data['slug'], $project, $catalog);
        $existing->fill($this->projectFields($data));
        $existing->legacy_source_id = $record['legacy_source_id'];
        $existing->images = $this->uploadedImages($request, $record['image_paths'], $data['slug']);
        if ($request->boolean('remove_video')) {
            $existing->video_path = null;
        }
        if ($request->hasFile('video')) {
            $existing->video_path = $this->storeUpload($request->file('video'), 'videos/'.$data['slug']);
        }
        $existing->updated_by = $request->user()->id;
        $existing->save();

        $this->recordActivity($request, 'portfolio.updated', $existing, 'Updated portfolio project '.$existing->title.'.');

        return redirect()->route('admin.portfolio')->with('success', 'Portfolio project updated.');
    }

    private function validatedData(Request $request, ?PortfolioProject $existing = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'not_regex:/^\d+$/', Rule::unique('portfolio_projects', 'slug')->ignore($existing?->id)],
            'vehicle_make' => ['required', 'string', 'max:100'],
            'vehicle_model' => ['required', 'string', 'max:160'],
            'vehicle_year' => ['nullable', 'integer', 'min:1950', 'max:'.(now()->year + 1)],
            'work_order' => ['nullable', 'string', 'max:100'],
            'upgrade_type' => ['required', 'string', 'max:190'],
            'project_date' => ['nullable', 'date'],
            'service_area' => ['nullable', 'string', 'max:190'],
            'client_name' => ['nullable', 'string', 'max:190'],
            'description' => ['required', 'string', 'max:50000'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'keep_images' => ['nullable', 'array', 'max:16'],
            'keep_images.*' => ['string', 'max:500'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:102400'],
            'remove_video' => ['nullable', 'boolean'],
            'is_published' => ['required', 'boolean'],
        ]);
    }

    private function projectFields(array $data): array
    {
        return [
            'title' => trim($data['title']),
            'slug' => trim($data['slug']),
            'vehicle_make' => trim($data['vehicle_make']),
            'vehicle_model' => trim($data['vehicle_model']),
            'vehicle_year' => $data['vehicle_year'] ?? null,
            'work_order' => filled($data['work_order'] ?? null) ? trim($data['work_order']) : null,
            'upgrade_type' => trim($data['upgrade_type']),
            'project_date' => $data['project_date'] ?? null,
            'service_area' => filled($data['service_area'] ?? null) ? trim($data['service_area']) : null,
            'client_name' => filled($data['client_name'] ?? null) ? trim($data['client_name']) : null,
            'description' => trim($data['description']),
            'is_published' => (bool) $data['is_published'],
        ];
    }

    private function findOrNewProject(string $key, array $record): PortfolioProject
    {
        if (str_starts_with($key, 'legacy-')) {
            return PortfolioProject::firstOrNew(['legacy_source_id' => $record['legacy_source_id']], [
                'title' => $record['title'],
                'slug' => $record['slug'],
                'vehicle_make' => $record['vehicle_make'],
                'vehicle_model' => $record['vehicle_model'],
                'vehicle_year' => $record['vehicle_year'],
                'work_order' => $record['work_order'],
                'upgrade_type' => $record['upgrade_type'],
                'project_date' => $record['project_date'],
                'service_area' => $record['service_area'],
                'client_name' => $record['client_name'],
                'description' => $record['description'],
                'images' => $record['image_paths'],
                'video_path' => $record['video_path'],
                'is_published' => $record['is_published'],
            ]);
        }

        return PortfolioProject::findOrFail($key);
    }

    private function uploadedImages(Request $request, array $existingPaths, string $slug): array
    {
        $keep = collect($request->input('keep_images', []))->intersect($existingPaths)->values();
        $uploads = collect($request->file('images', []))
            ->map(fn (UploadedFile $file) => $this->storeUpload($file, 'images/'.$slug))->values();
        $images = $keep->concat($uploads)->unique()->values();
        $coverImage = (string) $request->input('cover_image', '');
        if (Str::startsWith($coverImage, 'upload:')) {
            $coverImage = (string) $uploads->get((int) substr($coverImage, 7), '');
        }
        if ($coverImage !== '' && $images->contains($coverImage)) {
            $images = collect([$coverImage])->concat($images->reject(fn (string $image) => $image === $coverImage))->values();
        }

        return $images->all();
    }

    private function storeUpload(UploadedFile $file, string $directory): string
    {
        $name = Str::uuid().'.'.strtolower($file->extension());
        $path = 'uploads/portfolio/'.$directory.'/'.$name;
        $bucket = config('filesystems.disks.s3.bucket');

        if ($bucket) {
            $root = trim((string) config('filesystems.disks.s3.root', ''), '/');
            $stream = fopen($file->getRealPath(), 'rb');
            try {
                Storage::disk('s3')->getClient()->putObject([
                    'Bucket' => $bucket,
                    'Key' => trim($root.'/'.$path, '/'),
                    'Body' => $stream,
                    'ContentType' => $file->getMimeType(),
                ]);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        } else {
            $targetDirectory = public_path(dirname($path));
            if (! is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }
            $file->move($targetDirectory, $name);
        }

        return $path;
    }

    private function ensureSlugIsUnique(string $slug, ?string $currentAdminKey, PortfolioCatalog $catalog): void
    {
        $duplicate = $catalog->all(true)->first(fn (array $project) => $project['slug'] === $slug && $project['admin_key'] !== $currentAdminKey);
        if ($duplicate) {
            throw ValidationException::withMessages(['slug' => 'This page slug is already used by another portfolio project.']);
        }
    }

    private function recordActivity(Request $request, string $action, PortfolioProject $project, string $summary): void
    {
        DB::table('admin_activity_logs')->insert([
            'user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => 'portfolio_project',
            'subject_id' => (string) $project->id,
            'summary' => $summary,
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
