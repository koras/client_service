<?php

namespace App\Http\Controllers;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Contracts\Services\CertificatePdfServiceInterface;
use App\Contracts\Services\CertificateServiceInterface;
use App\Contracts\Services\FileServiceInterface;
use App\Contracts\Services\OrderSendingServiceInterface;
use App\Contracts\Services\WidgetServiceInterface;
use App\DTO\PreviewPdfRequestDto;
use App\Enums\ContentDispositionEnum;
use App\Enums\ErrorsEnum;
use App\Enums\DeliveryTypeEnum;
use App\Enums\ResponseStatusEnum;
use App\Exceptions\RateLimitException;
use App\Http\Requests\GetWidgetInfoRequest;
use App\Http\Requests\ParseFromXlsxRequest;
use App\Http\Requests\PromoCodeRequest;
use App\Http\Requests\SendPinSmsRequest;
use App\Http\Requests\GetCftCertificateInfoRequest;
use App\Http\Requests\ShowPreviewPdfRequest;
use App\Http\Requests\UploadCustomCoverRequest;
use App\Logging\WidgetLogObject;
use App\Services\GenerationPdfApiService;
use App\Services\ProductsDataService;
use Error;
use Exception;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as ResultResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ClientController extends Controller
{
    /**
     * Получить данные виджета для отображения на фронте
     *
     * @param GetWidgetInfoRequest $request
     * @param WidgetServiceInterface $widgetService
     * @param string|null $term
     * @return JsonResponse
     */
    public function getWidgetInfo(
        GetWidgetInfoRequest $request,
        WidgetServiceInterface $widgetService,
        string $term = null
    ): JsonResponse {
        $log = WidgetLogObject::make('Start getWidgetInfo for term: ' . $term, 'getWidgetInfo');
        Log::error($log->message, $log->toContext());
        try {

            $cacheKey = "widget_info_{$term}_{$request->input('active_nominal')}";
            $res = Cache::remember($cacheKey, 180, function () use ($widgetService, $request, $term) {
                return $widgetService->getWidgetInfo($request, $term);
            });
        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('Error getWidgetInfo: ' . $e->getMessage(), 'getWidgetInfo');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::INTERNAL_SERVER_ERROR, $e->getMessage());
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }

        return response()->json($res);
    }

    /**
     * Парсинг Xls для заказа с типом "рассылка"
     *
     * @param ParseFromXlsxRequest $request
     * @param OrderSendingServiceInterface $orderSendingService
     * @return JsonResponse
     */
    public function parseFromXlsx(
        ParseFromXlsxRequest $request,
        OrderSendingServiceInterface $orderSendingService
    ): JsonResponse {
        $file = $request->file('file');
        $recipientType = DeliveryTypeEnum::tryFrom($request->recipientType);

        try {
            if (!$orderSendingService->importFromXls($file, $recipientType)) {
                $log = WidgetLogObject::make(
                    'Error from parseXls recipients: В файле присутствуют ошибки',
                    'parseFromXlsx'
                );
                Log::error($log->message, $log->toContext());

                $this->error->setError(ErrorsEnum::FILE_NOT_SAVED, 'В файле присутствуют ошибки');
                $this->response['status'] = ResponseStatusEnum::error->name;
                $this->response['data'] = $orderSendingService->getErrorRows();
                return response()->json($this->response, $this->error->getRequestCode());
            }
        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('Error from parseXls recipients: ' . $e->getMessage(), 'parseFromXlsx');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::FILE_NOT_SAVED, $e->getMessage());
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }

        $this->response['data'] = $orderSendingService->getValidRows();
        return response()->json($this->response);
    }

    /**
     * Данные для фронта - страница просмотр Сертификата
     *
     * @param string $widgetId
     * @param string $id
     * @param CertificateServiceInterface $certificateService
     * @return JsonResponse
     */
    public function showCertificate(
        string $widgetId,
        string $id,
        CertificateServiceInterface $certificateService
    ): JsonResponse {
        try {
            $dataResource = $certificateService->getDataForShowCertificate($id);
            if ($this->error->exist()) {
                $this->response['status'] = ResponseStatusEnum::error->name;
                $this->response['data'] = $this->error->getToArray();
                $this->response['message'] = $this->error->getMessage();
                $this->response['message'] = $this->error->getMessage();
                return response()->json($this->response, $this->error->getRequestCode());
            }
        } catch (Throwable|Error $e) {
            $this->error->setError(ErrorsEnum::DATA_PROCESSING_ERROR, $e->getMessage());
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }

        return response()->json($dataResource);
    }

    /**
     * Получить PDF по id сертификата из сервиса генерации PDF
     *
     * @param string $id
     * @param Request $request
     * @param CertificatePdfServiceInterface $certificatePdfService
     * @return JsonResponse|ResultResponse
     */
    public function showPdf(
        string $id,
        Request $request,
        CertificatePdfServiceInterface $certificatePdfService
    ): ResultResponse|JsonResponse {
        $responseApi = $certificatePdfService->getPdfByCertificateId($id);
        if ($this->error->exist()) {
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }

        $isDownload = !empty($request->query('download'));
        $contentDisposition = ContentDispositionEnum::getByIsDownloadFlag($isDownload);

        return new ResultResponse(
            $responseApi->body(),
            $responseApi->status(),
            [
                'Content-Type' => GenerationPdfApiService::CONTENT_TYPE_APPLICATION_PDF,
                'Content-Disposition' => "$contentDisposition->value; filename=\"$id.pdf\""
            ]
        );
    }

    public function previewCertificatePdf(
        string $id,
        ShowPreviewPdfRequest $request,
        CertificatePdfServiceInterface $certificatePdfService
    ): ResultResponse|JsonResponse {
        $requestDto = PreviewPdfRequestDto::fromRequest($request);
        $responseApi = $certificatePdfService->getPreviewPdf($id, $requestDto);
        if ($this->error->exist()) {
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }

        return new ResultResponse(
            $responseApi->body(),
            $responseApi->status(),
            $responseApi->headers()
        );
    }

    public function products()
    {
    }

    /**
     * Загрузка кастомной обложки для сертификата
     *
     * @param string $id
     * @param UploadCustomCoverRequest $request
     * @param FileServiceInterface $fileService
     * @param WidgetRepositoryInterface $widgetRepository
     * @return JsonResponse
     */
    public function uploadCustomCover(
        string $id,
        UploadCustomCoverRequest $request,
        FileServiceInterface $fileService,
        WidgetRepositoryInterface $widgetRepository
    ): JsonResponse {
        try {
            $file = $request->file('file');
            /** @var WidgetInterface $widget */
            $widget = $widgetRepository->find($id);

            $resultDto = $fileService->uploadCustomCover($file, $widget);
            $this->response['data'] = $resultDto->toArray();
        } catch (RecordsNotFoundException $e) {
            $log = WidgetLogObject::make(
                'Widget not found: ' . $id . ' Error: ' . $e->getMessage(),
                'uploadCustomCover'
            );
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::WIDGET_NOT_FOUND);
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make(
                'File not saved for widget: ' . $id . ' Error: ' . $e->getMessage(),
                'uploadCustomCover'
            );
            Log::error($log->message, $log->toContext());
            $this->error->setError(ErrorsEnum::FILE_NOT_SAVED, $e->getMessage());
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }

        return response()->json($this->response);
    }

    public function getProducts(
        string $id,
        ProductsDataService $productsDataService,
        WidgetRepositoryInterface $widgetRepository
    ): JsonResponse {
        try {
            /** @var WidgetInterface $widget */
            $widget = $widgetRepository->find($id);
            $products = $productsDataService->getAvailableProductsByWidget($widget);
            $productsJson = $productsDataService->convertProductsToCustomArray($products);
        } catch (RecordsNotFoundException $e) {
            $log = WidgetLogObject::make('Widget not found: ' . $id . ' Error: ' . $e->getMessage(), 'getProducts');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::WIDGET_NOT_FOUND);
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make(
                'Error from getProducts: ' . $id . ' Error: ' . $e->getMessage(),
                'getProducts'
            );
            Log::error($log->message, $log->toContext());
            $this->error->setError(ErrorsEnum::INTERNAL_SERVER_ERROR, $e->getMessage());
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }

        return response()->json($productsJson);
    }

    /**
     * @param PromoCodeRequest $request
     * @param ProductsDataService $productsDataService
     * @param string $id
     * @param WidgetRepositoryInterface $widgetRepository
     * @return JsonResponse
     */
    public function promoProducts(
        PromoCodeRequest $request,
        ProductsDataService $productsDataService,
        string $id,
        WidgetRepositoryInterface $widgetRepository
    ): JsonResponse {
        /** @var WidgetInterface $widget */
        $widget = $widgetRepository->find($id);

        if (!$widget->promo) {
            $this->error->setError(ErrorsEnum::PROMO_CODE_NOT_FOUND);
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response);
        }

        try {
            $promoCode = $request->input('promo_code');
            $products = $request->input('products');
            $promoProduct = $productsDataService->getPromoProduct($products, $promoCode);
            $this->response['data'] = $promoProduct;
            $this->response['status'] = ResponseStatusEnum::ok->name;

        } catch (Exception|RecordsNotFoundException $e) {
            $this->error->setError(ErrorsEnum::PROMO_CODE_NOT_FOUND);
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();

        }
        return response()->json($this->response);
    }

    /**
     * @param SendPinSmsRequest $request
     * @param CertificateServiceInterface $certificateService
     * @param CertificateRepositoryInterface $certificateRepository
     * @return JsonResponse
     */
    public function sendPinSms(
        SendPinSmsRequest $request,
        CertificateServiceInterface $certificateService,
        CertificateRepositoryInterface $certificateRepository
    ): JsonResponse {
        $phone = $request->input('phone');
        $certificateId = $request->input('certificateId');

        try {
            $certificate = $certificateRepository->find($certificateId);
            $certificateService->sendPinSms($phone, $certificate, $certificate->orderItem);
            return response()->json($this->response);
        } catch (RateLimitException $e) {
            $log = WidgetLogObject::make(
                'CertificateId: ' . $certificateId . ' Error: ' . $e->getMessage(),
                'sendPinSms'
            );

            Log::error($log->message, $log->toContext());

            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = ErrorsEnum::MAXIMUM_ATTEMPTS_EXCEEDED;

            return response()->json($this->response);
        } catch (RecordsNotFoundException $e) {
            $log = WidgetLogObject::make(
                'Certificate not found: ' . $certificateId . ' Error: ' . $e->getMessage(),
                'sendPinSms'
            );
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::CERTIFICATE_NOT_FOUND);
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response);
        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('Error from sendPinSms:  Error: ' . $e->getMessage(), 'sendPinSms');
            Log::error($log->message, $log->toContext());
            $this->error->setError(ErrorsEnum::INTERNAL_SERVER_ERROR, $e->getMessage());
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }
    }


    /**
     * @param GetCftCertificateInfoRequest $request
     * @param CertificateServiceInterface $certificateService
     * @param CertificateRepositoryInterface $certificateRepository
     * @return JsonResponse
     */
    public function getCftCertificateInfo(
        GetCftCertificateInfoRequest $request,
        CertificateServiceInterface $certificateService,
        CertificateRepositoryInterface $certificateRepository
    ): JsonResponse {
        $serialCertificate = $request->input('certificateNumber');
        $hashRequest = $request->input('hash');
        try {
            $result = $certificateService->cftCertificateInfo($certificateRepository, $serialCertificate, $hashRequest);
            $this->response['data'] = $result;
            return response()->json($this->response);
        } catch (RecordsNotFoundException $e) {
            $log = WidgetLogObject::make(
                'Certificate not found: ' . $serialCertificate . ' Error: ' . $e->getMessage(),
                'getCftCertificateInfo'
            );
            Log::error($log->message, $log->toContext());
            $this->error->setError(ErrorsEnum::CERTIFICATE_NOT_FOUND);
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response);
        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make(
                'Error from getCftCertificateInfo:  Error: ' . $e->getMessage(),
                'getCftCertificateInfo'
            );
            Log::error($log->message, $log->toContext());
            $this->error->setError(ErrorsEnum::INTERNAL_SERVER_ERROR, $e->getMessage());
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }
    }


}
