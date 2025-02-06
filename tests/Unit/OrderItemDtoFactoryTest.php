<?php

namespace Unit;

use App\Contracts\Models\OrderInterface;
use App\Contracts\Services\OrderItemDtoBuilderInterface;
use App\DTO\OrderItemDto;
use App\Enums\FileStoragePathEnum;
use App\Enums\WidgetDeliveryVariantsEnum;
use Tests\ForTest\ProductsApiTESTService;
use Tests\TestCase;

class OrderItemDtoFactoryTest extends TestCase
{
    private OrderItemDtoBuilderInterface $orderItemDtoFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderItemDtoFactory = app(OrderItemDtoBuilderInterface::class);
    }

    /**
     * @covers \App\Services\OrderItemDtoBuilder::build
     * @return void
     */
    public function testCreateOrderItemFromCreateRequestSuccess(): void
    {
        $widget = $this->createMockWidget();
        $widget->id = ProductsApiTESTService::WIDGET_ID;
        $widget->delivery_variants = array_column(WidgetDeliveryVariantsEnum::cases(), 'name');
        $order = $this->createMockOrder();
        $order->widget = $widget;

        // Устанавливаем 2 доступных товара в настройках виджета
        $widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000]);

        $orderItemDto = $this->orderItemDtoFactory->build(self::REQUEST_ORDER_ITEM, $order, $widget);
        self::assertInstanceOf(OrderItemDto::class, $orderItemDto);
        self::assertEquals(self::REQUEST_ORDER_ITEM['certificate']['message'], $orderItemDto->message);
        self::assertEquals(self::REQUEST_ORDER_ITEM['certificate']['basket_key'], $orderItemDto->basketKey);
        self::assertEquals(self::REQUEST_ORDER_ITEM['certificate']['product']['product_id'], $orderItemDto->productId);
        self::assertEquals(self::REQUEST_ORDER_ITEM['certificate']['product']['quantity'], $orderItemDto->quantity);
        self::assertEquals(self::REQUEST_ORDER_ITEM['certificate']['delivery_type'], $orderItemDto->deliveryType);
        self::assertEquals(self::REQUEST_ORDER_ITEM['recipient']['type'], $orderItemDto->recipientTypeEnum->value);
        self::assertEquals(self::REQUEST_ORDER_ITEM['recipient']['name'], $orderItemDto->recipientName);
        self::assertEquals(self::REQUEST_ORDER_ITEM['recipient']['msisdn'], $orderItemDto->recipientMsisdn);
        self::assertEquals(self::REQUEST_ORDER_ITEM['sender']['name'], $orderItemDto->senderName);
        self::assertEquals(self::REQUEST_ORDER_ITEM['sender']['email'], $orderItemDto->senderEmail);
        self::assertNotEmpty($orderItemDto->amount);
        self::assertNull($orderItemDto->tiberiumOrderId);
        self::assertEquals(FileStoragePathEnum::CustomCoverDir->value . '/' . self::REQUEST_ORDER_ITEM['certificate']['template_src'], $orderItemDto->cover);
        self::assertInstanceOf(OrderInterface::class, $orderItemDto->order);
        self::assertEquals($order->id, $orderItemDto->order->id);
    }

    /**
     * Проверяем, что стоимость не зависит от переданного параметра amount в запросе
     * Стоимость рассчитывается из параметров товара (полученных и ProductService)
     *
     * @return void
     */
    public function testPriceValueFromCreateOrderItemFromCreateRequest()
    {
        $widget = $this->createMockWidget();
        $widget->id = ProductsApiTESTService::WIDGET_ID;
        $widget->delivery_variants = array_column(WidgetDeliveryVariantsEnum::cases(), 'name');
        $order = $this->createMockOrder();
        $order->widget = $widget;

        // Устанавливаем 2 доступных товара в настройках виджета
        $widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000]);

        $data = self::REQUEST_ORDER_ITEM;
        $data['certificate']['product']['amount'] = 50;
        $orderItemDto = $this->orderItemDtoFactory->build($data, $order, $widget);

        self::assertNotEquals($data, $orderItemDto->amount);
        self::assertEquals(ProductsApiTESTService::PRODUCTS_DATA[ProductsApiTESTService::PRODUCT_ID_1000]['price'], $orderItemDto->amount);
    }

}
