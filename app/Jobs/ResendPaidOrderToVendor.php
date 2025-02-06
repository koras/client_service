<?php

namespace App\Jobs;

use App\Contracts\JobsHandlers\PaidOrderTransferToVendorHandlerInterface;
use App\Contracts\Repositories\PromoCodesRepositoryInterface;
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
 * Задание переотправки заказа в ПЦ:
 */
class ResendPaidOrderToVendor implements ShouldQueue
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
        PromoCodesRepositoryInterface $promoCodesRepository,
        ProductsApiServiceInterface $productsApiService
    ): void
    {
        $log = WidgetLogObject::make('Start handle ResendPaidOrderToVendor for order:' . $this->orderId, 'ResendPaidOrderToVendor');
        Log::info($log->message, $log->toContext());

        try {
            $order = $jobHandler->getOrderById($this->orderId);

            // Публикуем заказ в ПЦ
            foreach ($order->orderItems as $orderItem) {
                if($order->promo_code_id){
                    $productPromoCode = $promoCodesRepository->find($order->promo_code_id);
                    $products = $productsApiService->getProductsDataByIds([$productPromoCode->product]);
                    $product = $products->first();
                    $orderItem->amount = $product->price;
                }
                $externalOrderId = $jobHandler->sendOrderItemToVendor($orderItem);
                $jobHandler->saveExternalOrderIdInOrderItem($orderItem, $externalOrderId);
            }

        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('Error handle ResendPaidOrderToVendor for order:' . $this->orderId . ' :' . $e->getMessage(), 'ResendPaidOrderToVendor');
            Log::info($log->message, $log->toContext());
            throw new Exception($e->getMessage());
        }

        $log = WidgetLogObject::make('Success handle ResendPaidOrderToVendor for order:' . $this->orderId, 'ResendPaidOrderToVendor', $order);
        Log::info($log->message, $log->toContext());
    }

}
