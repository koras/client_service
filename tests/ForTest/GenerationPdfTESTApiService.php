<?php

namespace Tests\ForTest;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Services\GenerationPdfApiServiceInterface;
use App\DTO\PreviewPdfDataDto;
use App\Services\GenerationPdfApiService;
use Illuminate\Http\Client\Response;

class GenerationPdfTESTApiService implements GenerationPdfApiServiceInterface
{

    public function generatePdf(CertificateInterface $certificate, bool $isDownload): ?Response
    {
        $statusCode = 200;
        $headers = ['Content-Type' => GenerationPdfApiService::CONTENT_TYPE_APPLICATION_PDF];
        $body = ''; // Здесь может быть содержимое PDF файла, если нужно

        return new Response(new \GuzzleHttp\Psr7\Response(
            $statusCode,
            $headers,
            $body,
        ));
    }

    public function generatePreviewPdf(PreviewPdfDataDto $dataDto): ?Response
    {
        $statusCode = 200;
        $headers = ['Content-Type' => GenerationPdfApiService::CONTENT_TYPE_APPLICATION_PDF];
        $body = ''; // Здесь может быть содержимое PDF файла, если нужно

        return new Response(new \GuzzleHttp\Psr7\Response(
            $statusCode,
            $headers,
            $body,
        ));
    }
}