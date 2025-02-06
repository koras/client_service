<?php

namespace App\Jobs;

use App\Contracts\JobsHandlers\PaidOrderTransferToVendorHandlerInterface;
use App\Contracts\Repositories\PromoCodesRepositoryInterface;
use App\Contracts\Services\LifeCycleServiceInterface;
use App\Contracts\Services\ProductsApiServiceInterface;
use App\Logging\WidgetLogObject;
use Error;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Задание обработки оплаченного заказа:
 * 1. Публикация заказа в ПЦ
 * 2. Обновление количества товаров
 */
class PaidOrderTransferToVendor implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $orderId
    )
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     * @throws Exception
     */
    public function handle(
        PaidOrderTransferToVendorHandlerInterface $jobHandler,
        LifeCycleServiceInterface $lifeCycle,
        PromoCodesRepositoryInterface $promoCodesRepository,
        ProductsApiServiceInterface $productsApiService
    ): void
    {
        $log = WidgetLogObject::make('Start handle PaidOrderTransferToVendor for order:' . $this->orderId, 'PaidOrderTransferToVendor');
        Log::info($log->message, $log->toContext());

        try {
            $order = $jobHandler->getOrderById($this->orderId);

            $log = WidgetLogObject::make('Get order for handle PaidOrderTransferToVendor:' . $order->id, 'PaidOrderTransferToVendor');
            Log::info($log->message, $log->toContext());

            // Публикуем заказ в ПЦ
            foreach ($order->orderItems as $orderItem) {
                $log = WidgetLogObject::make('Start foreach handler $orderItem for handle PaidOrderTransferToVendor:' . $orderItem->id, 'PaidOrderTransferToVendor', $orderItem);
                Log::info($log->message, $log->toContext());
                if($order->promo_code_id){
                    $productPromoCode = $promoCodesRepository->find($order->promo_code_id);
                    $products = $productsApiService->getProductsDataByIds([$productPromoCode->product]);
                    $product = $products->first();
                    $orderItem->amount = $product->price;
                }
                $externalOrderId = $jobHandler->sendOrderItemToVendor($orderItem);
                $jobHandler->saveExternalOrderIdInOrderItem($orderItem, $externalOrderId);
            }

            // Обновляем данные по товарам для виджета, для которого был создан заказ
            if(!$order->widget->flexible_nominal){
                $jobHandler->reduceProductsByOrder($order);
            }
        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('Error handle PaidOrderTransferToVendor for order:' . $this->orderId . ' :' . $e->getMessage(), 'PaidOrderTransferToVendor');
            Log::info($log->message, $log->toContext());
            throw new Exception($e->getMessage());
        }

        $lifeCycle->createStatus($this->orderId,"samara",14 );
        $log = WidgetLogObject::make('Success handle PaidOrderTransferToVendor for order:' . $this->orderId, 'PaidOrderTransferToVendor', $order);
        Log::info($log->message, $log->toContext());
    }

}
