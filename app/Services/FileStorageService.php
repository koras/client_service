<?php

namespace App\Services;

use App\Contracts\Services\FileStorageServiceInterface;
use App\Enums\FileStoragePathEnum;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileStorageService implements FileStorageServiceInterface
{
    public function save(UploadedFile|File $file, FileStoragePathEnum $storagePathEnum, string $fileName, ?string $fileFolder = null): ?string
    {
        $fullPath = $this->makeFullPath($storagePathEnum, $fileName, $fileFolder);
        $options = ['Content-Type' => $file->getMimeType()];

        $result = Storage::disk('s3')->put($fullPath, file_get_contents($file), $options);
        if (!$result) {
            return null;
        }

        return $fullPath;
    }

    public function getFileContent(string $filePath): ?string
    {
        return  Storage::disk('s3')->get($filePath);
    }

    public function isExists(string $filePath): bool
    {
        return Storage::disk('s3')->exists($filePath);
    }

    public function getUrlByPath(string $path): string
    {
        return Storage::disk('s3')->url($path);
    }

    private function makeFullPath(FileStoragePathEnum $storagePathEnum, string $fileName, ?string $fileFolder = null): string
    {
        if ($fileFolder) {
            return $storagePathEnum->value . '/' . $fileFolder . '/' . $fileName;
        }

        return $storagePathEnum->value . '/' . $fileName;
    }
}