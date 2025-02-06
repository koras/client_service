<?php

namespace App\Contracts\Services;

use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface XlsParseServiceInterface
{
    /**
     * @throws Exception
     */
    public function parseOrderSenderXlsToArray(UploadedFile $file, int $maxRowsCount = 0, int $maxColumnsCount = 0, int $headerRowsCount = 0): array;
}