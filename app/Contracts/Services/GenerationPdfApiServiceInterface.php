<?php

namespace App\Contracts\Services;

use App\Contracts\Models\CertificateInterface;
use App\DTO\PreviewPdfDataDto;
use Illuminate\Http\Client\Response;

interface GenerationPdfApiServiceInterface
{
    public function generatePdf(CertificateInterface $certificate): ?Response;

    public function generatePreviewPdf(PreviewPdfDataDto $dataDto): ?Response;
}
