<?php

namespace App\Services;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Services\ErrorServiceInterface;
use App\Contracts\Services\FileServiceInterface;
use App\Contracts\Services\FileStorageServiceInterface;
use App\DTO\FileUploadResultDto;
use App\Enums\ErrorsEnum;
use App\Enums\FileStoragePathEnum;
use App\Traits\PathGeneratorTrait;
use Carbon\Carbon;
use Error;
use Exception;
use Illuminate\Http\UploadedFile;
use Throwable;

class FileService implements FileServiceInterface
{
    use PathGeneratorTrait;
    public function __construct(
        private readonly ErrorServiceInterface $error,
        private readonly FileStorageServiceInterface $storageService,
    )
    {
    }


    /**
     * @param UploadedFile $file
     * @param WidgetInterface $widget
     * @return FileUploadResultDto
     * @throws Exception
     */
    public function uploadCustomCover(UploadedFile $file, WidgetInterface $widget): FileUploadResultDto
    {
        try {
            $destinationFolder = $this->getCustomCoverFilesPath($widget);
            $fileName = (new Carbon())->timestamp . '.' . $file->extension();

            $path = $this->storageService->save($file, FileStoragePathEnum::UserFiles, $fileName, $destinationFolder);
            $fileUrl = $this->getCustomCoverDirUrl($widget) . '/' . $fileName;
        } catch (Throwable|Error $e) {
            $this->error->setError(ErrorsEnum::FILE_NOT_SAVED, 'Не удалось сохранить файл ');
            throw new Exception($this->error->getMessage());
        }

        return new FileUploadResultDto($fileUrl, $fileName);
    }
}