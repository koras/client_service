<?php

namespace App\Services;

use App\Contracts\Models\OrderItemInterface;
use App\Contracts\Services\CommitOrderPCDtoFactoryInterface;
use App\Contracts\Services\LifeCycleServiceInterface;
use App\Contracts\Services\ProductsApiServiceInterface;
use App\DTO\CommitOrderPCDto;
use App\DTO\ProductDto;
use App\Logging\WidgetLogObject;
use App\Traits\UniqueGeneratorTrait;
use Illuminate\Support\Facades\Log;

readonly class CommitOrderPCDtoFactory implements CommitOrderPCDtoFactoryInterface
{
    use UniqueGeneratorTrait;

    public function __construct(
        private ProductsApiServiceInterface $productsApiService,
        private LifeCycleServiceInterface $lifeCycle
    )
    {
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return CommitOrderPCDto
     */
    public function createDtoFromOrderItem(OrderItemInterface $orderItem): CommitOrderPCDto
    {
        $phoneNumber = str_replace('+', '', $orderItem->recipient_msisdn);

        $log = WidgetLogObject::make('createDtoFromOrderItem $phoneNumber:' . $phoneNumber, 'PaidOrderTransferToVendor');
        Log::debug($log->message, $log->toContext());

            $this->lifeCycle->createStatus($orderItem-> widget_order_id,"samara",9, $phoneNumber ." " . $orderItem->recipient_email);
        try {
            $product = $this->getProduct($orderItem);

        } catch (\Throwable|\Error $e) {
            $log = WidgetLogObject::make('Error createDtoFromOrderItem: ' . $orderItem->id . ' getProduct: ' . $e->getMessage(), 'PaidOrderTransferToVendor');
            Log::info($log->message, $log->toContext());
        }
        $externalOrderId = $orderItem->tiberium_order_id ?? $this->generateUniqueExternalOrderId();

        return new CommitOrderPCDto(
            orderNumber: $externalOrderId,
            productDto: $product,
            quantity: $orderItem->quantity,
            email: $orderItem->recipient_email,
            phoneNumber: $phoneNumber,
            additionalInfo: null,
            cardPaymentAmount: null,
        );
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return ProductDto
     */
    private function getProduct(OrderItemInterface $orderItem): ProductDto
    {
        $log = WidgetLogObject::make('product_id:' . $orderItem->product_id, 'PaidOrderTransferToVendorTest');
        Log::info($log->message, $log->toContext());
        return $this->productsApiService->getProductDataById($orderItem->product_id);
    }
}
