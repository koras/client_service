<?php

namespace App\Services;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Services\ErrorServiceInterface;
use App\Contracts\Services\GenerationPdfApiServiceInterface;
use App\DTO\PreviewPdfDataDto;
use App\Enums\ErrorsEnum;
use App\Enums\HttpMethodEnum;
use App\Logging\WidgetLogObject;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Класс для работы с API сервиса генерации PDF
 */
class GenerationPdfApiService implements GenerationPdfApiServiceInterface
{
    private const string API_METHOD_GENERATE_PDF_BY_CERTIFICATE = '/api/certificate/pdf/';
    private const string API_METHOD_GENERATE_PREVIEW_PDF = '/api/certificate/pdf/preview';
    public const string CONTENT_TYPE_APPLICATION_PDF = 'application/pdf';
    private string $host;

    public function __construct(
        private readonly ErrorServiceInterface $error,
    )
    {
        $this->host = config('generation-pdf-api.host');
    }

    /**
     * @param CertificateInterface $certificate
     * @return Response|null
     */
    public function generatePdf(CertificateInterface $certificate): ?Response
    {
        $methodApi = self::API_METHOD_GENERATE_PDF_BY_CERTIFICATE . $certificate->id;
        return $this->sendToApi(HttpMethodEnum::METHOD_GET, $methodApi);
    }

    /**
     * @param PreviewPdfDataDto $dataDto
     * @return Response|null
     */
    public function generatePreviewPdf(PreviewPdfDataDto $dataDto): ?Response
    {
        return $this->sendToApi(HttpMethodEnum::METHOD_POST, self::API_METHOD_GENERATE_PREVIEW_PDF, $dataDto->toArray());
    }

    /**
     * @param HttpMethodEnum $httpMethodEnum
     * @param string $methodApi
     * @param array $body
     * @return Response|null
     */
    private function sendToApi(HttpMethodEnum $httpMethodEnum, string $methodApi, array $body = []): ?Response
    {
        $methodHttp = $httpMethodEnum->value;

        $response = Http::$methodHttp(
            $this->host . $methodApi,
            $body
        );

        $log = WidgetLogObject::make('Send response to GenerationPdf /' . $methodApi, 'GenerationPdfApiService');
        Log::info($log->message, $log->toContext());

        return $this->getContext($response);
    }

    private function getContext(?Response $response): ?Response
    {
        if (empty($response)) {
            $log = WidgetLogObject::make('Empty response from GenerationPdf', 'GenerationPdfApiService');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::GENERATION_PDF_SERVICE_ERROR);
            return null;
        }

        if ($response->status() !== 200) {
            $log = WidgetLogObject::make('Error code response from GenerationPdf: ' . $response->status(), 'GenerationPdfApiService');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::GENERATION_PDF_SERVICE_ERROR);
            return null;
        }

        $headers = $response->headers();
        if (empty($headers['Content-Type'][0]) || $headers['Content-Type'][0] !== self::CONTENT_TYPE_APPLICATION_PDF) {
            $log = WidgetLogObject::make('Error Content-Type response from GenerationPdf: ', 'GenerationPdfApiService');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::GENERATION_PDF_SERVICE_ERROR);
            return null;
        }

        return $response;
    }
}