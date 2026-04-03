<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function upload(UploadedFile $file, string $folder = 'evidens'): string
    {
        $subFolder = date('Y/m');
        $path = "{$folder}/{$subFolder}";

        if ($this->isImage($file)) {
            return $this->compressAndSaveAsWebp($file, $path);
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($path, $filename, 'public');
    }

    private function isImage(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), [
            'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
        ]);
    }

    private function compressAndSaveAsWebp(UploadedFile $file, string $path): string
    {
        $image = match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($file->getPathname()),
            'image/png'  => imagecreatefrompng($file->getPathname()),
            'image/gif'  => imagecreatefromgif($file->getPathname()),
            'image/bmp'  => imagecreatefrombmp($file->getPathname()),
            'image/webp' => imagecreatefromwebp($file->getPathname()),
            default      => null,
        };

        if (!$image) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            return $file->storeAs($path, $filename, 'public');
        }

        $filename = Str::uuid() . '.webp';
        $fullPath = storage_path("app/public/{$path}");

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        imagewebp($image, "{$fullPath}/{$filename}", 80);
        imagedestroy($image);

        return "{$path}/{$filename}";
    }
}
