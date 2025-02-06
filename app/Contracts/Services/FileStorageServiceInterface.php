<?php

namespace App\Contracts\Services;

use App\Enums\FileStoragePathEnum;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

interface FileStorageServiceInterface
{
    public function save(UploadedFile|File $file, FileStoragePathEnum $storagePathEnum, string $fileName, ?string $fileFolder = null): ?string;

    public function getFileContent(string $filePath): ?string;

    public function isExists(string $filePath): bool;

    public function getUrlByPath(string $path);
}