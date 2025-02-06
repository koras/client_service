<?php

namespace Feature;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Enums\ResponseStatusEnum;
use App\Repositories\WidgetRepository;
use Illuminate\Support\Facades\App;
use Tests\ForTest\PaymentTESTService;
use Tests\ForTest\ProductsApiTESTService;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    private WidgetRepositoryInterface $widgetRepository;
    private WidgetInterface $widget;

    protected function setUp(): void
    {
        parent::setUp();

        $this->widgetRepository = $this->createMock(WidgetRepository::class);
        App::instance(WidgetRepositoryInterface::class, $this->widgetRepository);

        $this->widget = $this->createMockWidget();
        $this->widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000]);
        $this->widget->usage_rules = self::TEST_USAGE_RULES_DATA;
        $this->widget->id = ProductsApiTESTService::WIDGET_ID;
    }

    /**
     * @covers \App\Http\Controllers\ClientController::getWidgetInfo
     */
    public function testGetWidgetInfoWithSaleLabels(): void
    {
        $this->widgetRepository
            ->method('find')
            ->with($this->widget->id)
            ->willReturn($this->widget);

        $requestData = [
            self::REQUEST_ORDER_ITEM,
        ];
        $response = $this->post('/widget/' . $this->widget->id . '/order', $requestData);
        $response->assertStatus(200);

        $json = $response->json();
        self::assertEquals(ResponseStatusEnum::ok->name, $json['status']);
        self::assertEquals('success', $json['message']);
        self::assertNull($json['data']);
        self::assertArrayHasKey('confirmation_token', $json);
        self::assertEquals(PaymentTESTService::CONFIRMATION_TOKEN, $json['confirmation_token']);
    }

}
