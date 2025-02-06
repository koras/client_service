<?php

namespace Tests\ForTest;

use App\Contracts\Services\FileStorageServiceInterface;
use App\Enums\FileStoragePathEnum;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class FileStorageTESTService implements FileStorageServiceInterface
{

    public function save(File|UploadedFile $file, FileStoragePathEnum $storagePathEnum, string $fileName, ?string $fileFolder = null): ?string
    {
        return $this->makeFullPath($storagePathEnum, $fileName, $fileFolder);
    }

    public function getFileContent(string $filePath): ?string
    {
        // TODO: Implement getFileContent() method.
    }

    public function isExists(string $filePath): bool
    {
        return true;
    }

    public function getUrlByPath(string $path)
    {
        // TODO: Implement getUrlByPath() method.
    }

    private function makeFullPath(FileStoragePathEnum $storagePathEnum, string $fileName, ?string $fileFolder = null): string
    {
        if ($fileFolder) {
            return $storagePathEnum->value . '/' . $fileFolder . '/' . $fileName;
        }

        return $storagePathEnum->value . '/' . $fileName;
    }
}