<?php

namespace App\Services;

use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\WidgetInterface;
use App\Contracts\Services\OrderItemDtoBuilderInterface;
use App\Contracts\Services\ProductsApiServiceInterface;
use App\DTO\OrderItemDto;
use App\DTO\ProductDto;
use App\Enums\FileStoragePathEnum;
use App\Enums\RecipientTypeEnum;
use App\Enums\WidgetDeliveryVariantsEnum;
use App\Repositories\ProductsNominal;
use App\Models\ProductsNominal as PN;
use App\Traits\CleanDataTrait;
use App\Traits\PathGeneratorTrait;
use Exception;
use Throwable;

class OrderItemDtoBuilder implements OrderItemDtoBuilderInterface
{
    use PathGeneratorTrait;
    use CleanDataTrait;

    private $productsNominal;

    public function __construct(
        private readonly ProductsApiServiceInterface $productsApiService,
    )
    {
        $this->pn = new PN;
        $this->productsNominal = new ProductsNominal($this->pn);
    }

    private ProductDto $product;


    /**
     * @param array $requestOrderItem
     * @param OrderInterface $order
     * @param WidgetInterface $widget
     * @return OrderItemDto
     * @throws Exception
     */
    public function build(array $requestOrderItem, OrderInterface $order, WidgetInterface $widget): OrderItemDto
    {
        $this->isProductIdAvailableForWidget($requestOrderItem, $widget);
        $this->product = $this->receiveProductDataFromApi($requestOrderItem, $widget);

        return $this->createOrderItemDtoFromCreateRequest($requestOrderItem, $order, $widget);
    }

    /**
     * @param $requestOrderItem
     * @param $widget
     * @return int|mixed|string
     */
    private function getProductId($requestOrderItem, $widget): mixed
    {
        if ($widget->flexible_nominal) {
            $productNominal = $this->productsNominal->findOneBy(['nominal' => $requestOrderItem['certificate']['product']['amount']]);
            $product = $this->productsApiService->getProductsDataByPositionId($productNominal->position_id);
            return $product->id;
        }

        return $requestOrderItem['certificate']['product']['product_id'];
    }

    /**
     * @param array $requestOrderItem
     * @param OrderInterface $order
     * @param WidgetInterface $widget
     * @return OrderItemDto
     * @throws Exception
     */
    private function createOrderItemDtoFromCreateRequest(array $requestOrderItem, OrderInterface $order, WidgetInterface $widget): OrderItemDto
    {
        $productId = $this->getProductId($requestOrderItem, $widget);
        $amount = $this->getAmount($requestOrderItem, $widget);
        $cover = $this->getCover($requestOrderItem, $widget);
        $recipientMsisdn = $requestOrderItem['recipient']['msisdn'] ?? '+70000000000';
        $recipientMsisdn = $this->cleanPhone($recipientMsisdn);
        $recipientEmail = $requestOrderItem['recipient']['email'] ?? $requestOrderItem['sender']['email'];
        $recipientName = $requestOrderItem['recipient']['name'] ?? 'no name';
        $timeToSend = isset($requestOrderItem['time_to_send']) ? (new \DateTime($requestOrderItem['time_to_send']))->setTimezone(new \DateTimeZone('Europe/Moscow')) : new \DateTime();

        return new OrderItemDto(
            message: $requestOrderItem['certificate']['message'] ?? null,
            order: $order,
            basketKey: $requestOrderItem['certificate']['basket_key'],
            productId: $productId,
            quantity: $requestOrderItem['certificate']['product']['quantity'] ?? 1,
            deliveryType: $requestOrderItem['certificate']['delivery_type'] ?? 'Email',
            recipientTypeEnum: RecipientTypeEnum::tryFrom($requestOrderItem['recipient']['type']) ?? RecipientTypeEnum::Me,
            recipientName: $recipientName,
            recipientEmail: $recipientEmail,
            recipientMsisdn: $recipientMsisdn,
            senderName: $requestOrderItem['sender']['name'] ?? $recipientName,
            senderEmail: $requestOrderItem['sender']['email'] ?? $recipientEmail,
            timeToSend: $timeToSend,
            deliveredAt: null,
            amount: $amount,
            tiberiumOrderId: null,
            cover: $cover,
            utm: $item['utm'] ?? null,
            flexibleNominal: $widget->flexible_nominal
        );

    }

    /**
     * Получить данные товара из ProductServiceApi
     *
     * @param array $requestOrderItem
     * @param WidgetInterface $widget
     * @return ProductDto
     * @throws Exception
     */
    private function receiveProductDataFromApi(array $requestOrderItem, WidgetInterface $widget): ProductDto
    {
        $product = $this->productsApiService->getProductDataById($requestOrderItem['certificate']['product']['product_id']);
        if (!$product) {
            throw new Exception('Invalid product id');
        }

        return $product;
    }

    /**
     * @param array $requestOrderItem
     * @param WidgetInterface $widget
     * @return int
     * @throws Exception
     */
    private function getAmount(array $requestOrderItem, WidgetInterface $widget): int
    {
        if (WidgetDeliveryVariantsEnum::isCharity($widget->delivery_variants[0]) || $widget->flexible_nominal || !empty($requestOrderItem['certificate']['promo_code'])) {
            return $requestOrderItem['certificate']['product']['amount'];
        }

        return $this->product->price;
    }

    /**
     * @param array $requestOrderItem
     * @param WidgetInterface $widget
     * @return bool
     * @throws Exception
     */
    private function isProductIdAvailableForWidget(array $requestOrderItem, WidgetInterface $widget): bool
    {
        $productIds = $widget->getProductsAsArray();
        if (!empty($requestOrderItem['certificate']['promo_code'])) {
            return true;
        }
        if (!in_array($requestOrderItem['certificate']['product']['product_id'], $productIds)) {
            throw new Exception('Invalid product id - not available for widget');
        }

        return true;
    }

    private function getCover(array $requestOrderItem, WidgetInterface $widget): ?string
    {
        try {
            if (!empty($requestOrderItem['certificate']['template_src'])) {
                return FileStoragePathEnum::CustomCoverDir->value . '/' . $requestOrderItem['certificate']['template_src'];
            }

            if (isset($requestOrderItem['certificate']['template_id'])) {
                $templateId = (int) $requestOrderItem['certificate']['template_id'];
                return $this->getTemplateCover($templateId, $widget);
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }

    private function getTemplateCover(int $templateId, WidgetInterface $widget): ?string
    {
        $covers = $widget->covers;
        if (empty($covers)) {
            return null;
        }

        return $covers[$templateId]['file'] ?? null;
    }
}
