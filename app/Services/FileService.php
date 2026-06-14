<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class FileService
{
    public function upload(
        UploadedFile $file,
        string $directory,
        ?string $oldFile = null,
        ?int $resizeMax = null,
        string $disk = 'public',
    ): string {
        if ($oldFile) {
            $this->delete($oldFile, $disk);
        }

        $now = now();
        $folder = trim($directory, '/').'/'.$now->format('Y/m');
        $name = Str::random(40).'.'.$file->getClientOriginalExtension();
        $path = $folder.'/'.$name;

        if ($resizeMax && str_starts_with($file->getMimeType() ?? '', 'image/')) {
            try {
                $img = Image::read($file->getRealPath());
                $img->scaleDown(width: $resizeMax, height: $resizeMax);
                Storage::disk($disk)->put($path, (string) $img->encode());

                return $path;
            } catch (\Throwable) {
                // Fallback to plain store if resizing fails
            }
        }

        return $file->storeAs($folder, $name, $disk) ?: $path;
    }

    public function delete(?string $path, string $disk = 'public'): bool
    {
        if (! $path) {
            return false;
        }

        return Storage::disk($disk)->delete($path);
    }
}
