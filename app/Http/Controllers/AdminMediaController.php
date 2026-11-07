<?php

namespace App\Http\Controllers;

use App\Support\MediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminMediaController extends Controller
{
    public function index(): View
    {
        $assets = DB::table('site_media_assets')->orderBy('id')->get();
        $assets->each(function (object $asset): void {
            $asset->url = str_starts_with($asset->path, 'images/') ? asset($asset->path) : MediaUrl::url($asset->path);
        });

        return view('admin.media', compact('assets'));
    }

    public function update(Request $request, string $assetKey): RedirectResponse
    {
        $asset = DB::table('site_media_assets')->where('asset_key', $assetKey)->first();
        abort_unless($asset, 404);

        $validated = $request->validate([
            'file' => [$asset->type === 'video' ? 'required' : 'required', 'file', 'max:20480', $asset->type === 'video' ? 'mimetypes:video/mp4' : 'mimes:jpg,jpeg,png,webp'],
        ]);

        $file = $validated['file'];
        $path = $this->store($file, $assetKey);
        DB::table('site_media_assets')->where('id', $asset->id)->update(['path' => $path, 'updated_at' => now()]);

        return redirect()->route('admin.media')->with('success', $asset->label.' was updated.');
    }

    private function store(UploadedFile $file, string $assetKey): string
    {
        $extension = strtolower($file->extension());
        $name = $assetKey.'.'.$extension;
        $path = 'uploads/site-media/'.$name;

        if (config('filesystems.disks.s3.bucket')) {
            $root = trim((string) config('filesystems.disks.s3.root', ''), '/');
            \Illuminate\Support\Facades\Storage::disk('s3')->getClient()->putObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => trim($root.'/'.$path, '/'),
                'Body' => file_get_contents($file->getRealPath()),
                'ContentType' => $file->getMimeType(),
            ]);
        } else {
            $directory = public_path('uploads/site-media');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $file->move($directory, $name);
        }

        return $path;
    }
}
