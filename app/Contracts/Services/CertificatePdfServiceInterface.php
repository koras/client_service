<?php

namespace App\Contracts\Services;

use App\DTO\PreviewPdfRequestDto;
use Illuminate\Http\Client\Response;

interface CertificatePdfServiceInterface
{
    public function getPdfByCertificateId(string $id): ?Response;

    public function getPreviewPdf(string $id, PreviewPdfRequestDto $requestDto): ?Response;

}
