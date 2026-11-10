<?php

namespace App\Support;

use App\Models\PortfolioProject;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PortfolioCatalog
{
    public function all(bool $includeUnpublished = false): Collection
    {
        $overrides = PortfolioProject::query()->whereNotNull('legacy_source_id')->get()->keyBy('legacy_source_id');
        $legacyProjects = DB::table('legacy_archive_records')->where('source_table', 'portfolio')->orderBy('source_id')->get(['source_id', 'payload'])
            ->map(function ($row) use ($overrides) {
                try {
                    $record = json_decode(Crypt::decryptString($row->payload), true) ?: [];
                    $record['id'] ??= (string) $row->source_id;
                    $record['title'] = html_entity_decode(trim((string) ($record['title'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $record['content'] = $this->cleanDescription((string) ($record['content'] ?? ''));
                    $legacySourceId = (string) $row->source_id;
                    $override = $overrides->get($legacySourceId);
                    $title = $override?->title ?: $record['title'];
                    $legacySlug = trim((string) ($record['slug'] ?? $record['slung'] ?? ''));
                    if ($legacySlug === '' || ctype_digit($legacySlug)) {
                        $legacySlug = Str::slug($title);
                    }
                    $slug = $override?->slug ?: $legacySlug;
                    $inferredVehicle = $this->inferVehicle($title.' '.$record['content']);
                    $imagePaths = $override && $override->images !== null
                        ? $override->images
                        : $this->legacyImagePaths($record);
                    $description = $override?->description ?? $record['content'];
                    $videoPath = $override ? $override->video_path : ($record['video_path'] ?? null);

                    return [
                        'id' => $legacySourceId,
                        'admin_key' => $override ? (string) $override->id : 'legacy-'.$legacySourceId,
                        'legacy_source_id' => $legacySourceId,
                        'title' => $title,
                        'slug' => $slug,
                        'vehicle_make' => $override ? $override->vehicle_make : ($record['vehicle_make'] ?? $inferredVehicle['make']),
                        'vehicle_model' => $override ? $override->vehicle_model : ($record['vehicle_model'] ?? $inferredVehicle['model']),
                        'vehicle_year' => $override ? $override->vehicle_year : ($record['vehicle_year'] ?? $inferredVehicle['year']),
                        'work_order' => $override ? $override->work_order : ($record['work_order'] ?? null),
                        'upgrade_type' => $override ? $override->upgrade_type : ($record['upgrade_type'] ?? $this->inferUpgradeType($title.' '.$description)),
                        'project_date' => $override ? $override->project_date?->format('Y-m-d') : $this->normalizeDate($record['project_date'] ?? $record['date'] ?? null),
                        'service_area' => $override ? $override->service_area : ($record['service_area'] ?? $record['location'] ?? null),
                        'client_name' => $override ? $override->client_name : ($record['client_name'] ?? $record['client'] ?? null),
                        'description' => $description,
                        'content' => $description,
                        'service' => $record['service'] ?? null,
                        'image_paths' => $imagePaths,
                        'images' => collect($imagePaths)->map(fn ($path) => $this->mediaUrl($path))->filter()->values()->all(),
                        'video_path' => $videoPath,
                        'video_url' => $videoPath ? $this->mediaUrl($videoPath) : null,
                        'is_published' => $override?->is_published ?? true,
                        'is_legacy' => true,
                    ];
                } catch (\Throwable) {
                    return null;
                }
            })->filter(fn ($record) => is_array($record) && filled($record['title'] ?? null));

        $newProjects = PortfolioProject::query()->whereNull('legacy_source_id')->orderByDesc('project_date')->orderByDesc('id')->get()
            ->map(fn (PortfolioProject $project) => $this->fromModel($project));

        return $legacyProjects->concat($newProjects)->filter(fn (array $project) => $includeUnpublished || $project['is_published'])->values();
    }

    public function findForAdmin(string $adminKey): ?array
    {
        return $this->all(true)->first(fn (array $project) => $project['admin_key'] === $adminKey);
    }

    private function fromModel(PortfolioProject $project): array
    {
        $imagePaths = $project->images ?? [];

        return [
            'id' => (string) $project->id,
            'admin_key' => (string) $project->id,
            'legacy_source_id' => null,
            'title' => $project->title,
            'slug' => $project->slug,
            'vehicle_make' => $project->vehicle_make,
            'vehicle_model' => $project->vehicle_model,
            'vehicle_year' => $project->vehicle_year,
            'work_order' => $project->work_order,
            'upgrade_type' => $project->upgrade_type,
            'project_date' => $project->project_date?->format('Y-m-d'),
            'service_area' => $project->service_area,
            'client_name' => $project->client_name,
            'description' => $project->description,
            'content' => $project->description,
            'service' => null,
            'image_paths' => $imagePaths,
            'images' => collect($imagePaths)->map(fn ($path) => $this->mediaUrl($path))->filter()->values()->all(),
            'video_path' => $project->video_path,
            'video_url' => $project->video_path ? $this->mediaUrl($project->video_path) : null,
            'is_published' => $project->is_published,
            'is_legacy' => false,
        ];
    }

    private function legacyImagePaths(array $record): array
    {
        return collect($record)->filter(fn ($value, $key) => preg_match('/^image_/', (string) $key) && filled($value))
            ->map(function ($value): string {
                $path = trim((string) $value);
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }

                $filename = ltrim($path, '/');
                $filename = str_starts_with($filename, 'uploads/portfolio/')
                    ? substr($filename, strlen('uploads/portfolio/'))
                    : $filename;
                $localPath = 'uploads/portfolio/'.str_replace(':', '_', $filename);

                return is_file(public_path($localPath)) ? $localPath : 'uploads/portfolio/'.$filename;
            })->unique()->values()->all();
    }

    private function mediaUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return is_file(public_path($path)) ? asset($path) : MediaUrl::url($path);
    }

    private function cleanDescription(string $content): string
    {
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = preg_replace('/<\/?(?:p|div|li|h[1-6]|section|article)\b[^>]*>|<br\s*\/?\s*>/i', "\n", $content);
        $content = strip_tags($content);
        $content = preg_replace('/(?<![A-Za-z])(?:rnrn|rn)(?![A-Za-z])/i', "\n\n", $content);
        $content = preg_replace('/\s*—\s*/u', ', ', $content);

        return trim(preg_replace('/\n{3,}/', "\n\n", preg_replace('/[ \t]+/', ' ', $content)));
    }

    private function inferVehicle(string $text): array
    {
        $vehicles = [
            ['make' => 'Toyota', 'model' => 'Axio'],
            ['make' => 'Toyota', 'model' => 'Fielder'],
            ['make' => 'Toyota', 'model' => 'Passo'],
            ['make' => 'Subaru', 'model' => 'Forester XT'],
            ['make' => 'Mazda', 'model' => 'Demio'],
            ['make' => 'Nissan', 'model' => 'March'],
        ];

        foreach ($vehicles as $vehicle) {
            if (str_contains(strtolower($text), strtolower($vehicle['model']))) {
                preg_match('/\b(?:19|20)\d{2}\b/', $text, $year);

                return $vehicle + ['year' => $year[0] ?? null];
            }
        }

        return ['make' => null, 'model' => null, 'year' => null];
    }

    private function inferUpgradeType(string $text): ?string
    {
        $text = strtolower($text);
        if (str_contains($text, 'full system')) {
            return 'Full car audio system upgrade';
        }
        if (str_contains($text, 'underseat subwoofer')) {
            return 'Car radio and under-seat subwoofer upgrade';
        }
        if (str_contains($text, 'android radio')) {
            return 'Android car radio upgrade';
        }
        if (str_contains($text, 'car audio') || str_contains($text, 'sound')) {
            return 'Car audio upgrade';
        }

        return null;
    }

    private function normalizeDate(mixed $date): ?string
    {
        if (! filled($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
