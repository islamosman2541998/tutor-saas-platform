<?php

namespace App\Support\Files;

use App\Support\Tenancy\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Every tenant-owned upload (subject images, student photos, slider images,
 * receipts, ...) goes through here instead of hand-rolling storage calls
 * per module, so the folder layout (tenants/{tenant_id}/{directory}/...),
 * random filenames, and old-file cleanup stay consistent everywhere.
 */
class TenantUploads
{
    public function __construct(protected TenantContext $context) {}

    /**
     * @param  string  $directory  e.g. "subjects", "students", "website/sliders"
     * @return string  the stored path, relative to the public disk root
     */
    public function store(UploadedFile $file, string $directory): string
    {
        $tenantId = $this->context->id() ?? 'central';
        $filename = Str::random(40).'.'.$file->extension();

        return $file->storeAs(
            "tenants/{$tenantId}/{$directory}",
            $filename,
            'public',
        );
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Replaces an old file with a new upload, deleting the old one only
     * after the new one is safely stored.
     */
    public function replace(?string $oldPath, UploadedFile $newFile, string $directory): string
    {
        $newPath = $this->store($newFile, $directory);
        $this->delete($oldPath);

        return $newPath;
    }

    public function url(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
