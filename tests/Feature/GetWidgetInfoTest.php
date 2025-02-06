<?php

namespace Feature;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Contracts\Services\WidgetServiceInterface;
use App\Repositories\WidgetRepository;
use App\ValueObjects\WidgetSaleLabelObj;
use Illuminate\Support\Facades\App;
use Tests\ForTest\ProductsApiTESTService;
use Tests\TestCase;

class GetWidgetInfoTest extends TestCase
{
    private WidgetServiceInterface $widgetService;
    private WidgetRepositoryInterface $widgetRepository;
    private WidgetInterface $widget;

    protected function setUp(): void
    {
        parent::setUp();

        $this->widgetRepository = $this->createMock(WidgetRepository::class);
        App::instance(WidgetRepositoryInterface::class, $this->widgetRepository);

        $this->widgetService = app(WidgetServiceInterface::class);

        $this->widget = $this->createMockWidget();


        $this->widget->usage_rules = self::TEST_USAGE_RULES_DATA;
        $this->widget->id = ProductsApiTESTService::WIDGET_ID;
    }

    /**
     * @covers \App\Http\Controllers\ClientController::getWidgetInfo
     */
    public function testGetWidgetInfoWithSaleLabels(): void
    {
        $this->widgetRepository
            ->expects($this->once())
            ->method('findWidgetByDomainOrId')
            ->with($this->widget->id)
            ->willReturn($this->widget);

        $saleLabel = new WidgetSaleLabelObj(
            'sale.png',
            '#cecece',
            [ProductsApiTESTService::PRODUCT_ID_1000]
        );

        $this->widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000]);

        $this->widget->sale_label = $saleLabel;


        $response = $this->get('/widget/' . $this->widget->id);
        $response->assertStatus(200);

        $json = $response->json();
        self::assertNull($json['main']['active_nominal']);

        $amounts = $json['style']['amounts'];
        $firstAmount = $amounts[0];
        $lastAmount = $amounts[1];

        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_1000, $firstAmount['id']);
        self::assertNotNull($firstAmount['saleImage']);
        self::assertNotNull($firstAmount['saleColor']);

        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_3000, $lastAmount['id']);
        self::assertNull($lastAmount['saleImage']);
        self::assertNull($lastAmount['saleColor']);
    }


    /**
     * @covers \App\Http\Controllers\ClientController::getWidgetInfo
     */
    public function testGetWidgetInfoWithoutSaleLabels(): void
    {
        $this->widgetRepository
            ->expects($this->once())
            ->method('findWidgetByDomainOrId')
            ->with($this->widget->id)
            ->willReturn($this->widget);

        $saleLabel = new WidgetSaleLabelObj(
            null,
            null,
            null
        );

        $this->widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000]);

        $this->widget->sale_label = $saleLabel;

        $response = $this->get('/widget/' . $this->widget->id);
        $response->assertStatus(200);

        $json = $response->json();
        self::assertNull($json['main']['active_nominal']);

        $amounts = $json['style']['amounts'];
        $firstAmount = $amounts[0];
        $lastAmount = $amounts[1];

        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_1000, $firstAmount['id']);
        self::assertNull($firstAmount['saleImage']);
        self::assertNull($firstAmount['saleColor']);

        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_3000, $lastAmount['id']);
        self::assertNull($lastAmount['saleImage']);
        self::assertNull($lastAmount['saleColor']);
    }

    /**
     * @covers \App\Http\Controllers\ClientController::getWidgetInfo
     */
    public function testGetWidgetInfoWithActiveNominal(): void
    {
        $this->widgetRepository
            ->expects($this->once())
            ->method('findWidgetByDomainOrId')
            ->with($this->widget->id)
            ->willReturn($this->widget);

        $saleLabel = new WidgetSaleLabelObj(
            null,
            null,
            null
        );

        $this->widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000]);

        $this->widget->sale_label = $saleLabel;

        $response = $this->get('/widget/' . $this->widget->id . '?active_nominal=' . ProductsApiTESTService::PRODUCT_ID_1000);
        $response->assertStatus(200);

        $json = $response->json();
        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_1000, $json['main']['active_nominal']);
    }

    /**
     * @covers \App\Http\Controllers\ClientController::getWidgetInfo
     * @return void
     */
    public function testIssetAiSettings(): void
    {
        $this->widgetRepository
            ->expects($this->once())
            ->method('findWidgetByDomainOrId')
            ->with($this->widget->id)
            ->willReturn($this->widget);

        $this->widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000]);

        $saleLabel = new WidgetSaleLabelObj(
            null,
            null,
            null
        );
        $this->widget->sale_label = $saleLabel;


        $response = $this->get('/widget/' . $this->widget->id);
        $response->assertStatus(200);

        $json = $response->json();
        self::assertArrayHasKey('main', $json);
        $main = $json['main'];
        self::assertArrayHasKey('ai_image_enable', $main);
        self::assertArrayHasKey('ai_text_enable', $main);
    }

}
