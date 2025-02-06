<?php

namespace Tests\ForTest;

use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\OrderItemInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Services\ErrorServiceInterface;
use App\Contracts\Services\PaymentServiceInterface;
use App\Contracts\Services\QueueProducerInterface;
use App\Contracts\Services\VendorOrderServiceInterface;
use App\DTO\CreatePaymentDataDto;
use App\Enums\OrderPaymentStatusEnum;
use App\ValueObjects\OrderPaymentObj;
use Exception;
use Illuminate\Http\Request;
use YooKassa\Model\CurrencyCode;
use YooKassa\Model\Metadata;
use YooKassa\Model\Payment\ConfirmationType;
use YooKassa\Model\Payment\Payment;
use YooKassa\Model\Payment\PaymentInterface;
use YooKassa\Model\Payment\PaymentStatus;
use YooKassa\Model\Refund\RefundInterface;
use YooKassa\Request\Payments\AbstractPaymentResponse;
use YooKassa\Request\Payments\CreatePaymentResponse;

class PaymentTESTService implements PaymentServiceInterface
{
    public const int ACCOUNT_ID = 857636;
    public const int GATEWAY_ID = 1914620;
    public const string CONFIRMATION_TOKEN = 'ct-2d8e4f07-000f-5000-a000-191bd87c4033';
    public const string PAYMENT_ORDER_ID = '4a5a8a4c-b04d-4088-9ab9-70729dee7c55';
    public const string SUCCEEDED_PAYMENT_ID = '2dc6ee43-000f-5000-a000-1f9b68075a90';
    public const string CANCELED_PAYMENT_ID = '2dc6ee43-000f-5000-a000-1f9b68075a80';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly VendorOrderServiceInterface $vendorOrderService,
        private readonly ErrorServiceInterface $error,
        private readonly QueueProducerInterface $queueProducer,
    )
    {
    }

    public function createPayment(CreatePaymentDataDto $dataDto): AbstractPaymentResponse
    {
        $response = (new CreatePaymentResponse())
            ->setPaid(false)
            ->setTest(true)
            ->setAmount([
                'value' => 5000, //$dataDto->order->getTotalSum(),
                'currency' => CurrencyCode::RUB
            ])
            ->setStatus(PaymentStatus::PENDING)
            ->setMetadata([
                'host' => $dataDto->hostDto->host,
                'http_host' => $dataDto->hostDto->httpHost,
                'order_id' => $dataDto->order->id,
            ])
            ->setRecipient([
                'account_id' => self::ACCOUNT_ID,
                'gateway_id' => self::GATEWAY_ID
            ])
            ->setCreatedAt(new \DateTime())
            ->setRefundable(false)
            ->setConfirmation([
                'type' => ConfirmationType::EMBEDDED,
                'confirmation_token' => self::CONFIRMATION_TOKEN
            ]);

        return $response;
    }

    /**
     * @param PaymentInterface $callbackObj
     * @return bool
     * @throws Exception
     */
    public function isPaymentSuccessInExternalService(PaymentInterface $callbackObj): bool
    {
        if ($callbackObj->getId() == self::CANCELED_PAYMENT_ID) {
            throw new Exception('Payment status not Succeeded from PaymentInfo for Payment: ' . $callbackObj->getId());
        }

        if ($callbackObj->getId() == self::SUCCEEDED_PAYMENT_ID) {
            return true;
        }

        throw new Exception('Empty PaymentInfo for Payment: ' . $callbackObj->getId());
    }

    public function getCallbackObjectFromRequest(Request $request): PaymentInterface|RefundInterface
    {
        $payment = (new Payment())
            ->setStatus(PaymentStatus::SUCCEEDED)
            ->setMetadata(['order_id' => self::PAYMENT_ORDER_ID]);

        return $payment;
    }

    public function processCallback(PaymentInterface|RefundInterface $callbackObj): bool
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->find($callbackObj->getMetadata()['order_id']);
        if ($order->wasReceivedCallback()) {
            return false;
        }

        $statusEnum = OrderPaymentStatusEnum::tryFrom($callbackObj->getStatus());
        $this->orderRepository->updatePaymentStatus($order, $statusEnum);

        // Если платеж отменен - завершаем процесс
        if (!OrderPaymentStatusEnum::isSucceeded($callbackObj->getStatus())) {
            return true;
        }

        // Отправляем сообщение в очередь Получения сертификатов
        $this->queueProducer->publishOrderPaidQueue($order);
        return true;
    }
}