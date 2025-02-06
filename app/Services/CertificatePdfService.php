<?php

namespace App\Services;

use App\Contracts\Models\CertificateInterface;
use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Contracts\Services\CertificatePdfServiceInterface;
use App\Contracts\Services\ErrorServiceInterface;
use App\Contracts\Services\GenerationPdfApiServiceInterface;
use App\Contracts\Services\ProductsApiServiceInterface;
use App\DTO\PreviewPdfDataDto;
use App\DTO\PreviewPdfRequestDto;
use App\Enums\ErrorsEnum;
use App\Logging\WidgetLogObject;
use Error;
use Exception;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

readonly class CertificatePdfService implements CertificatePdfServiceInterface
{
    public function __construct(
        private GenerationPdfApiServiceInterface $pdfApiService,
        private CertificateRepositoryInterface $certificateRepository,
        private WidgetRepositoryInterface $widgetRepository,
        private ProductsApiServiceInterface $productsApiService,
        private ErrorServiceInterface $error,
    )
    {
    }

    /**
     * Получить PDF по id сертификата из сервиса генерации PDF
     *
     * @param string $id
     * @return Response|null
     */
    public function getPdfByCertificateId(string $id): ?Response
    {
        try {
            /** @var CertificateInterface $certificate */
            $certificate = $this->certificateRepository->find($id);

            $httpResponse = $this->pdfApiService->generatePdf($certificate);
            if ($this->error->exist()) {
                return null;
            }
        } catch (RecordsNotFoundException $e) {
            $log = WidgetLogObject::make('Certificate for generation showPDF not found: ' . $id . ' Error: ' . $e->getMessage(), 'showPdf');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::CERTIFICATE_NOT_FOUND);
            return null;
        } catch (Exception|Error $e) {
            $log = WidgetLogObject::make('Error for generation showPDF for certificate: ' . $id . ' Error: ' . $e->getMessage(), 'showPdf');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::GENERATION_PDF_SERVICE_ERROR);
            return null;
        }

        return $httpResponse;
    }

    public function getPreviewPdf(string $id, PreviewPdfRequestDto $requestDto): ?Response
    {
        try {
            /** @var WidgetInterface $widget */
            $widget = $this->widgetRepository->find($id);
            $product = $this->productsApiService->getProductDataById($requestDto->productId);

            $dataDto = new PreviewPdfDataDto(
                $widget->id,
                $requestDto->templateId,
                $product->nominal,
                $product->currency,
                $requestDto->download
            );

            $httpResponse = $this->pdfApiService->generatePreviewPdf($dataDto);
            if ($this->error->exist()) {
                return null;
            }
        } catch (RecordsNotFoundException $e) {
            $log = WidgetLogObject::make('Widget for generation previewPDF not found: ' . $id . ' Error: ' . $e->getMessage(), 'previewPdf');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::WIDGET_NOT_FOUND);
            return null;
        } catch (Exception|Error $e) {
            $log = WidgetLogObject::make('Error for generation previewPDF for widget: ' . $id . ' Error: ' . $e->getMessage(), 'previewPdf');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::GENERATION_PDF_SERVICE_ERROR);
            return null;
        }

        return $httpResponse;
    }
}