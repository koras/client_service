<?php

namespace Unit;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Contracts\Services\WidgetServiceInterface;
use App\Http\Requests\GetWidgetInfoRequest;
use App\Http\Resources\WidgetInfoResource;
use App\Repositories\WidgetRepository;
use App\ValueObjects\WidgetSaleLabelObj;
use Illuminate\Support\Facades\App;
use Tests\ForTest\ProductsApiTESTService;
use Tests\TestCase;

class WidgetServiceTest extends TestCase
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
        $this->widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000]);
        $saleLabel = new WidgetSaleLabelObj(
            'sale.png',
            '#cecece',
            [ProductsApiTESTService::PRODUCT_ID_1000]
        );
        $this->widget->sale_label = $saleLabel;
        $this->widget->usage_rules = self::TEST_USAGE_RULES_DATA;
        $this->widget->id = ProductsApiTESTService::WIDGET_ID;


    }

    /**
     * @covers \App\Services\WidgetService::getWidgetInfo
     * @return void
     */
    public function testGetWidgetInfo(): void
    {
        $this->widgetRepository
            ->expects($this->once())
            ->method('findWidgetByDomainOrId')
            ->with($this->widget->id)
            ->willReturn($this->widget);

        $request = new GetWidgetInfoRequest();
        $dataResource = $this->widgetService->getWidgetInfo($request, $this->widget->id);
        self::assertInstanceOf(WidgetInfoResource::class, $dataResource);

        $arrayData = $dataResource->jsonSerialize();
        self::assertNull($arrayData['main']['active_nominal']);
    }

    /**
     * @covers \App\Services\WidgetService::getWidgetInfo
     * @return void
     */
    public function testGetWidgetInfoWithActiveNominal(): void
    {
        $this->widgetRepository
            ->expects($this->once())
            ->method('findWidgetByDomainOrId')
            ->with($this->widget->id)
            ->willReturn($this->widget);

        $request = new GetWidgetInfoRequest();
        $request->query->add(['active_nominal' => ProductsApiTESTService::PRODUCT_ID_1000]);
        $dataResource = $this->widgetService->getWidgetInfo($request, $this->widget->id);
        self::assertInstanceOf(WidgetInfoResource::class, $dataResource);

        $arrayData = $dataResource->jsonSerialize();
        self::assertNotNull($arrayData['main']['active_nominal']);
        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_1000, $arrayData['main']['active_nominal']);
    }

}
