<?php

namespace App\Contracts\Services;

use App\Contracts\Models\WidgetInterface;
use App\DTO\FileUploadResultDto;
use Exception;
use Illuminate\Http\UploadedFile;

interface FileServiceInterface
{
    /**
     * @param UploadedFile $file
     * @param WidgetInterface $widget
     * @return FileUploadResultDto
     * @throws Exception
     */
    public function uploadCustomCover(UploadedFile $file, WidgetInterface $widget): FileUploadResultDto;
}