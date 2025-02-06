<?php

namespace App\Services;

use App\Contracts\Models\OrderInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Services\ErrorServiceInterface;
use App\Contracts\Services\LifeCycleServiceInterface;
use App\Contracts\Services\PaymentServiceInterface;
use App\Contracts\Services\QueueProducerInterface;
use App\DTO\CreatePaymentDataDto;
use App\Enums\ErrorsEnum;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\YookassaCallbackEventTypeEnum;
use App\Logging\WidgetLogObject;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use YooKassa\Client as YooKassaClient;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Common\Exceptions\BadApiRequestException;
use YooKassa\Common\Exceptions\ExtensionNotFoundException;
use YooKassa\Common\Exceptions\ForbiddenException;
use YooKassa\Common\Exceptions\InternalServerError;
use YooKassa\Common\Exceptions\NotFoundException;
use YooKassa\Common\Exceptions\ResponseProcessingException;
use YooKassa\Common\Exceptions\TooManyRequestsException;
use YooKassa\Common\Exceptions\UnauthorizedException;
use YooKassa\Model\Notification\NotificationFactory;
use YooKassa\Model\Payment\PaymentInterface;
use YooKassa\Model\Refund\RefundInterface;
use YooKassa\Request\Payments\CreatePaymentResponse;

readonly class PaymentService implements PaymentServiceInterface
{

    public function __construct(
        private YooKassaClient $ykClient,
        private NotificationFactory $factory,
        private ErrorServiceInterface $error,
        private OrderRepositoryInterface $orderRepository,
        private QueueProducerInterface $queueProducer,
        private LifeCycleServiceInterface $lifeCycle
    )
    {
    }

    /**
     * Создать платеж в Yookassa
     *
     * @param CreatePaymentDataDto $dataDto
     * @return CreatePaymentResponse
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function createPayment(CreatePaymentDataDto $dataDto): CreatePaymentResponse
    {
        $widget = $dataDto->order->widget;
        $ykDsn = $widget->ykassa_dsn;
        $this->ykClient->setAuth($ykDsn->login, $ykDsn->password);

        $this->lifeCycle->createStatus($dataDto->order->id,"samara",4 );
        return $this->ykClient->createPayment($dataDto->toArray(), $dataDto->order->id);

    }


    /**
     * Проверить, что у платежа статус = Succeeded во внешнем сервисе платежей (Yookassa)
     *
     * @param PaymentInterface $callbackObj
     * @return bool
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws Exception
     */
    public function isPaymentSuccessInExternalService(PaymentInterface $callbackObj): bool
    {
        $paymentInfo = $this->getPaymentInfo($callbackObj);
        if (!$paymentInfo) {
            throw new Exception('Empty PaymentInfo for Payment: ' . $callbackObj->getId());
        }

        if (!OrderPaymentStatusEnum::isSucceeded($paymentInfo->getStatus())) {
            throw new Exception('Payment status not Succeeded from PaymentInfo for Payment: ' . $callbackObj->getId());
        }

        return true;
    }

    /**
     * Конвертировать запрос callback от YooKassa в объект Payment
     *
     * @param Request $request
     * @return PaymentInterface|RefundInterface
     * @throws ApiException
     */
    public function getCallbackObjectFromRequest(Request $request): PaymentInterface|RefundInterface
    {
        $msg = json_decode($request->getContent(), true);

        $notification = $this->factory->factory($msg);
        if (!YookassaCallbackEventTypeEnum::isValidForProcess($notification->getEvent())) {
            $this->error->setError(ErrorsEnum::YOOKASSA_INVALID_EVENT_TYPE);
            $log = WidgetLogObject::make($this->error->getCode() . ' ' . $this->error->getMessage(), 'YookassaCallback');
            Log::info($log->message, $log->toContext());

            throw new ApiException();
        }

        $callbackObj = $notification->getObject();
        $log = WidgetLogObject::make('get callback from ykassa data:'. json_encode($callbackObj), 'YookassaCallback', null, $callbackObj?->getMetadata()['order_id'] ?? null);
        Log::info($log->message, $log->toContext());

        if (is_null($callbackObj)) {
            $this->error->setError(ErrorsEnum::YOOKASSA_EMPTY_CALLBACK_OBJ);
            $log = WidgetLogObject::make($this->error->getCode() . ' ' . $this->error->getMessage(), 'YookassaCallback');
            Log::info($log->message, $log->toContext());

            throw new ApiException();
        }

        return $callbackObj;
    }

    /**
     * Обработать объект платежа, полученный в callback от Yookassa
     *
     * @param PaymentInterface|RefundInterface $callbackObj
     * @return bool
     * @throws Exception
     */
    public function processCallback(PaymentInterface|RefundInterface $callbackObj): bool
    {
        $order = $this->getOrderFromCallbackObj($callbackObj);
        if ($order->wasReceivedCallback()) {
            return false;
        }

        $statusEnum = OrderPaymentStatusEnum::tryFrom($callbackObj->getStatus());
        $this->orderRepository->updatePaymentStatus($order, $statusEnum);

        // Если платеж отменен - завершаем процесс
        if (!OrderPaymentStatusEnum::isSucceeded($callbackObj->getStatus())) {

            $this->lifeCycle->createStatus($order->id,"samara",7 );
            return true;
        }

        // Отправляем сообщение в очередь Обработки оплаченных заказов
        $this->queueProducer->publishOrderPaidQueue($order);
        $this->lifeCycle->createStatus($order->id,"samara",8 );
        return true;
    }

    /**
     * Получить объект Order по данным из платежа (по ID заказа)
     *
     * @param PaymentInterface|RefundInterface $callbackObj
     * @return OrderInterface
     */
    private function getOrderFromCallbackObj(PaymentInterface|RefundInterface $callbackObj): OrderInterface
    {
        $orderId = $callbackObj->getMetadata()['order_id'];
        return $this->orderRepository->findByTrackingNumber($orderId);
    }

    /**
     * Получить информацию о платеже по Id платежа
     *
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws ApiException
     * @throws ExtensionNotFoundException
     * @throws BadApiRequestException
     * @throws InternalServerError
     * @throws ForbiddenException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    private function getPaymentInfo(PaymentInterface $callbackObj): ?PaymentInterface
    {
        $order = $this->getOrderFromCallbackObj($callbackObj);
        $widget = $order->widget;
        $ykDsn = $widget->ykassa_dsn;
        $paymentId = $callbackObj->getId();
        $this->ykClient->setAuth($ykDsn->login, $ykDsn->password);
        return $this->ykClient->getPaymentInfo($paymentId);
    }

}
