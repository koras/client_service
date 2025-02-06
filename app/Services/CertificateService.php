<?php

namespace App\Services;
use App\Contracts\Models\CertificateInterface;
use App\Contracts\Models\HistorySendInterface;
use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Contracts\Repositories\HistorySendRepositoryInterface;
use App\Contracts\Services\BarcodeGeneratorInterface;
use App\Contracts\Services\BarcodeGeneratorPdf417Interface;
use App\Contracts\Services\CertificateServiceInterface;
use App\Contracts\Services\ErrorServiceInterface;
use App\Contracts\Services\PCApiServiceInterface;
use App\Contracts\Services\QrCodeGeneratorInterface;
use App\DTO\HistorySendDto;
use App\DTO\ShowCertificateDataDto;
use App\DTO\CftCertificateInfoDto;
use App\Enums\ErrorsEnum;
use App\Enums\HistorySendStatusEnum;
use App\Exceptions\RateLimitException;
use App\Http\Resources\ShowCertificateResource;
use App\Logging\WidgetLogObject;
use App\Models\HistorySend;
use App\Traits\PathGeneratorTrait;
use Error;
use Exception;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Contracts\Services\HistorySendServiceInterface;

class CertificateService implements CertificateServiceInterface
{
    use PathGeneratorTrait;
    private const LIMIT_SEND_PIN_SMS = 5;
    public function __construct(
        private ErrorServiceInterface $error,
        private CertificateRepositoryInterface $certificateRepository,
        private BarcodeGeneratorInterface $barcodeGenerator,
        private QrCodeGeneratorInterface $qrCodeGenerator,
        private PCApiServiceInterface $PCApiService,
        private readonly HistorySend $historySend,
        private readonly HistorySendRepositoryInterface $historySendRepository,
        private readonly historySendServiceInterface $historySendService,
        private BarcodeGeneratorPdf417Interface $barcodeGeneratorPdf417,
    )
    {
    }

    /**
     * @param $certificate_id
     * @return bool
     */
    private function checkCountSendPinSms($certificate_id): bool
    {
        $sending = $this->historySendRepository->getSendsPinSms($certificate_id);
        $now = time();
        $yesterday = $now - (24 * 60 * 60);
        if(count($sending)){
            $filteredData = array_filter($sending->toArray(), function($item) use ($yesterday) {
                return strtotime($item['timestamp']) >= $yesterday;
            });
            return !(count($filteredData) > self::LIMIT_SEND_PIN_SMS);
        }
        return true;
    }

    /**
     * @param $phone
     * @param $certificate
     * @param $orderItem
     * @return HistorySendInterface
     * @throws RateLimitException
     */
    public function sendPinSms($phone, $certificate, $orderItem): HistorySendInterface
    {
        $historySendDto = HistorySendDto::forPinSmsMessage($orderItem, $certificate);
        if(!$this->checkCountSendPinSms($certificate->id)){
            throw new RateLimitException('The message sending limit has been exceeded');
        }
        try {
            $this->PCApiService->sendSmsPin($phone, $certificate->pin);
            $historySendDto->status = HistorySendStatusEnum::Success;
            $historySendDto->serviceStatus = 'ok';
        }catch (Exception $e) {
            $historySendDto->status = HistorySendStatusEnum::Error;
            $log = WidgetLogObject::make('Error for send Pin SMS :  Error: ' . $e->getMessage(), 'sendSmsPin');
            Log::error($log->message, $log->toContext());
            $this->error->setError(ErrorsEnum::ERROR_SENDING_TO_PC);
        }
        return $this->historySendService->create($historySendDto);
    }

    /**
     * @param CertificateRepositoryInterface $certificateRepository
     * @param string $serialCertificate
     * @param string $hashRequest
     * @return array|null
     */
    public function cftCertificateInfo(CertificateRepositoryInterface $certificateRepository, string $serialCertificate, string $hashRequest): ?array
    {
        $certificate = $certificateRepository->findOneBy(['serial' => $serialCertificate]);
        if(!$certificate){
            return throw new RecordsNotFoundException();
        }

        $widget = $certificate->orderItem->order->widget;
        $hashCertificate = md5($certificate->orderItem->order->widget->domain . '-' . $serialCertificate);
        if(!$widget->supplier_id){
            return throw new RecordsNotFoundException();
        }
        if($hashRequest != $hashCertificate){
            return throw new RecordsNotFoundException();
        }

        try {
            $dataResponse = $this->PCApiService->getCftCertificateInfo($widget->supplier_id, $serialCertificate);
            $CftCertificateInfoDto = new CftCertificateInfoDto();
            $dataDto = $CftCertificateInfoDto->CftCertificateInfoDtoByStrategy($dataResponse, $serialCertificate, $widget->name);
        }catch (Exception $e) {
            $log = WidgetLogObject::make('Error for getCftCertificateInfo :  Error: ' . $e->getMessage(), 'CertificateService');
            Log::error($log->message, $log->toContext());
            $this->error->setError(ErrorsEnum::ERROR_SENDING_TO_PC);
            return null;
        }
        return $dataDto;
    }


    /**
     * @param string $id
     * @return ShowCertificateResource|null
     */
    public function getDataForShowCertificate(string $id): ?ShowCertificateResource
    {
        try {
            /** @var CertificateInterface $certificate */
            $certificate = $this->certificateRepository->find($id);
            /** @var WidgetInterface $widget */
            $widget = $certificate->orderItem->order->widget;
            $cover = $this->getCoverFullUrl($certificate);
            $barcodeData = $this->getBarcodeData($certificate);
            $certificate->amount = !empty($certificate->amount) ? $certificate->amount : $certificate->nominal;
            $qr = $this->generateQrCode($barcodeData);
            $barcode = $this->generateBarcode($barcodeData);
            $barcodePdf417 = $this->generateBarcodePdf417($barcodeData);

            $dataDto = new ShowCertificateDataDto(
                $certificate,
                $cover,
                $qr,
                $barcode,
                $barcodePdf417
            );

            $dataResource = new ShowCertificateResource($dataDto);

        } catch (RecordsNotFoundException $e) {
            $log = WidgetLogObject::make('Certificate for showCertificate not found: ' . $id . ' Error: ' . $e->getMessage(), 'showCertificate');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::CERTIFICATE_NOT_FOUND);
            return null;
        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('Error for showCertificate: ' . $id . ' Error: ' . $e->getMessage(), 'showCertificate');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::DATA_PROCESSING_ERROR);
            return null;
        }

        return $dataResource;
    }

    private function getCoverFullUrl(CertificateInterface $certificate): string
    {
        $widget = $certificate->orderItem->order->widget;
        $coverDirUrl = $this->getCoverDirUrl($widget);
        return $coverDirUrl . $certificate->cover_path;
    }

    private function getBarcodeData(CertificateInterface $certificate): ?string
    {
        if (!empty($certificate->barcode)) {
            return $certificate->barcode;
        }

        return $certificate->serial;
    }

    private function generateQrCode(?string $data): ?string
    {
        return $this->qrCodeGenerator
            ->generate($data)
            ->getBase64();
    }

    private function generateBarcode(?string $data): ?string
    {
        return $this->barcodeGenerator
            ->generate($data)
            ->getBase64();
    }

    private function generateBarcodePdf417(?string $data): ?string
    {
        return $this->barcodeGeneratorPdf417
            ->generate($data)
            ->getBase64();
    }
}
