<?php

namespace App\Services;

use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\WidgetInterface;
use App\Contracts\Services\LifeCycleServiceInterface;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Contracts\Services\ErrorServiceInterface;
use App\Contracts\Services\OrderItemDtoBuilderInterface;
use App\Contracts\Services\OrderServiceInterface;
use App\Contracts\Services\PaymentServiceInterface;
use App\Contracts\Services\QueueProducerInterface;
use App\DTO\CreatePaymentDataDto;
use App\DTO\OrderDto;
use App\DTO\RequestHostDto;
use App\Enums\ErrorsEnum;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Logging\WidgetLogObject;
use App\Repositories\PromoCodesRepository;
use App\ValueObjects\OrderPaymentObj;
use Error;
use Exception;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LifeCycleService;
use Throwable;

readonly class OrderService implements OrderServiceInterface
{


    public function __construct(
        private ErrorServiceInterface $error,
        private OrderRepositoryInterface $orderRepository,
        private OrderItemRepositoryInterface $orderItemRepository,
        private OrderItemDtoBuilderInterface $orderItemDtoBuilder,
        private WidgetRepositoryInterface $widgetRepository,
        private PaymentServiceInterface $paymentService,
        private QueueProducerInterface $queueProducer,
        private LifeCycleServiceInterface $lifeCycle,
        private ProductsDataService $productsDataService
    )
    {
    }

    /**
     * @param OrderDto $orderDto
     * @param RequestHostDto $hostDto
     * @return OrderPaymentObj|null
     */
    public function createOrderProcess(OrderDto $orderDto, RequestHostDto $hostDto): ?OrderPaymentObj
    {
        try {
            $this->checkPromoOrder($orderDto);
        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('createOrderProcess Error: ' . $e->getMessage(), 'createOrder');
            Log::error($log->message, $log->toContext());
            $this->error->setError(ErrorsEnum::CREATE_ORDER_ERROR, $e->getMessage());
            return null;
        }

        DB::beginTransaction();
        try {
            // Создаем заказ в БД
            $order = $this->createOrderInDB($orderDto);
            $this->lifeCycle->createStatus($order->id,"samara",1, $order->widget_id);
            // Создаем платеж в YooKassa
            $createPaymentDto = CreatePaymentDataDto::fromOrderAndHostDto($order, $hostDto);
            $paymentResponse = $this->paymentService->createPayment($createPaymentDto);
            $paymentObj = OrderPaymentObj::fromCreatePaymentResponse($paymentResponse);

            $this->lifeCycle->createStatus($order->id,"samara",2, $order->widget_id);
            // Сохраняем данные платежа в заказе
            $this->orderRepository->updatePaymentData($order, $paymentObj);
            $this->lifeCycle->createStatus($order->id,"samara",26, $order->widget_id);

            DB::commit();
        } catch (Throwable|Error $e) {
            DB::rollBack();
            $log = WidgetLogObject::make('createOrderProcess Error: ' . $e->getMessage(), 'createOrder');
            Log::error($log->message, $log->toContext());
            $this->error->setError(ErrorsEnum::CREATE_ORDER_ERROR, 'Ошибка при создании заказа');
            return null;
        }

        return $paymentObj;
    }

    /**
     * @param OrderDto $createOrderDto
     * @return OrderInterface|null
     */
    public function createOrderInDB(OrderDto $createOrderDto): ?OrderInterface
    {

        $orderItems = $createOrderDto->orderItems;

        try {
            /** @var WidgetInterface $widget */
            $widget = $this->widgetRepository->find($createOrderDto->widgetId);
            /** @var OrderInterface $order */
            $order = $this->orderRepository->create($createOrderDto);
            foreach ($createOrderDto->orderItems as $orderItem) {
                $orderItemDto = $this->orderItemDtoBuilder->build($orderItem, $order, $widget);
                $orderItem = $this->orderItemRepository->create($orderItemDto);
            }
        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('createOrderInDB Error: ' . $e->getMessage(), 'createOrder');
            Log::error($log->message, $log->toContext());
            $this->error->setError(ErrorsEnum::CREATE_ORDER_ERROR, $e->getMessage());
            return null;
        }

        return $order;
    }

    /**
     * Переотправить заказ в ПЦ
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function resendOrderToVendor(OrderInterface $order): bool
    {
        if (!OrderPaymentStatusEnum::isSucceeded($order->payment_status)) {
            $this->error->setError(ErrorsEnum::ORDER_STATUS_INVALID);
            return false;
        }

        $this->lifeCycle->createStatus($order->id,"samara",3, $order->widget_id);
        $this->queueProducer->publishReorderQueue($order);
        return true;
    }

    /**
     * @param OrderDto $orderDto
     * @return string
     * @throws Exception
     */
    private function checkPromoOrder(OrderDto $orderDto): string
    {
        $widget = $this->widgetRepository->find($orderDto->widgetId);

        $promo = $orderProductsId = [];
        foreach ($orderDto->orderItems as $item) {
            if (!empty($item['certificate']['promo_code'])) {
                $item['certificate']['product']['quantity'] = 1;

                $product = $this->productsDataService->getDataPromoProduct($item['certificate']['product']['product_id'], $item['certificate']['promo_code']);
                if($item['certificate']['product']['amount'] != $product->price){
                    return throw new Exception('Ошибка в заказе');
                }
                $item['certificate']['needProduct'] = $product->needProduct;
                $promo[] = $item;
            }
            $orderProductsId[] = $item['certificate']['product']['product_id'];
        }

        if(empty($promo)){
            return '';
        }

        if (count($promo) > 1) {
            return throw new Exception('Ошибка в заказе');
        }
        if (count($promo) && !$widget->promo) {
            return throw new Exception('Акционный товар не действителен');
        }
        $promoItem = current($promo);
        if (!empty($promoItem['certificate']['needProduct']) && !in_array($promoItem['certificate']['needProduct'], $orderProductsId)) {
            return throw new Exception('Добавьте акционный товар в корзину');
        }

        return $promoItem['certificate']['promo_code'];
    }


    public function getPromoCodeByRequest($request): ?string
    {
        $array = $request->all();
        foreach ($array as $item) {
            if(!empty($item['certificate']['promo_code'])){
                return $this->productsDataService->getDataPromoProduct($item['certificate']['product']['product_id'], $item['certificate']['promo_code']);
            }
        }
        return null;
    }





}
