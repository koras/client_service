<?php

namespace App\DTO;

use Illuminate\Contracts\Support\Arrayable;

readonly class FileUploadResultDto implements Arrayable
{
    public function __construct(
        public string $fileUrl,
        public string $fileName,
    )
    {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'fileName' => $this->fileName,
            'fileUrl' => $this->fileUrl,
        ];
    }
}