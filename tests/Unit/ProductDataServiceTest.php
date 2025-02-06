<?php

namespace Unit;

use App\Contracts\Services\ProductsDataServiceInterface;
use App\DTO\ProductDto;
use Tests\ForTest\ProductsApiTESTService;
use Tests\TestCase;

class ProductDataServiceTest extends TestCase
{
    private ProductsDataServiceInterface $productsDataService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productsDataService = app(ProductsDataServiceInterface::class);
    }

    /**
     * @covers \App\Services\ProductsDataService::getAvailableProductsByWidget
     * @return void
     */
    public function testGetAvailableProductsByWidgetOne(): void
    {
        $widget = $this->createMockWidget();
        $widget->id = ProductsApiTESTService::WIDGET_ID;

        // Устанавливаем 1 доступный товар в настройках виджета
        $widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000]);

        $products = $this->productsDataService->getAvailableProductsByWidget($widget);
        $product = $products->first();

        // Проверяем, что вернулся 1 товар
        self::assertCount(1, $products);
        self::assertInstanceOf(ProductDto::class, $product);
    }

    /**
     * @covers \App\Services\ProductsDataService::getAvailableProductsByWidget
     * @return void
     */
    public function testGetAvailableProductsByWidgetAll(): void
    {
        $widget = $this->createMockWidget();
        $widget->id = ProductsApiTESTService::WIDGET_ID;

        // Устанавливаем 2 доступных товара в настройках виджета
        $widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000]);

        $products = $this->productsDataService->getAvailableProductsByWidget($widget);

        // Проверяем, что вернулись 2 товара
        self::assertCount(2, $products);
    }

    /**
     * @covers \App\Services\ProductsDataService::sortProductsByWidgetSettings
     * @return void
     */
    public function testSortProductsByWidgetSettingsASC(): void
    {
        $widget = $this->createMockWidget();
        $widget->id = ProductsApiTESTService::WIDGET_ID;

        // Устанавливаем порядок товаров для сортировки
        $widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_3000, ProductsApiTESTService::PRODUCT_ID_1000]);

        $products = $this->productsDataService->getAvailableProductsByWidget($widget);
        $products = $this->productsDataService->sortProductsByWidgetSettings($widget, $products);
        $productFirst = $products->first();
        $productLast = $products->last();

        // Проверяем, что вернулось 2 товара в том порядке который указан в настройках виджета
        self::assertCount(2, $products);
        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_3000, $productFirst->id);
        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_1000, $productLast->id);

    }

    /**
     * @covers \App\Services\ProductsDataService::sortProductsByWidgetSettings
     * @return void
     */
    public function testSortProductsByWidgetSettingsDESC(): void
    {
        $widget = $this->createMockWidget();
        $widget->id = ProductsApiTESTService::WIDGET_ID;

        // Устанавливаем порядок товаров для сортировки
        $widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000]);

        $products = $this->productsDataService->getAvailableProductsByWidget($widget);
        $products = $this->productsDataService->sortProductsByWidgetSettings($widget, $products);
        $productFirst = $products->first();
        $productLast = $products->last();

        // Проверяем, что вернулось 2 товара в том порядке который указан в настройках виджета
        self::assertCount(2, $products);
        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_1000, $productFirst->id);
        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_3000, $productLast->id);

    }

}
