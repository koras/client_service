<?php

namespace Unit;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Services\WidgetInfoServiceInterface;
use App\ValueObjects\WidgetSaleLabelObj;
use Tests\ForTest\ProductsApiTESTService;
use Tests\TestCase;

class WidgetInfoServiceTest extends TestCase
{
    private const array UNSORTED_COVERS_DATA = [
        [
            "sortOrder" => "1",
            "file" => "card1-61a7877a06408.png",
        ],
        [
            "sortOrder" => "0",
            "file" => "card3-61a7877a06fbf.png",
        ],
        [
            "sortOrder" => "4",
            "file" => "card6-61a7877a075b8.png",
        ],
        [
            "sortOrder" => "3",
            "file" => "card4-61a7877a07246.png",
        ],
        [
            "sortOrder" => "5",
            "file" => "card2-61a7877a06d27.png",
        ]
    ];

    private const string RULES_TEXT = "Test text for \r\n test \r\n and test for text";

    private WidgetInfoServiceInterface $widgetInfoService;
    private WidgetInterface $widget;

    protected function setUp(): void
    {
        parent::setUp();
        $this->widget = $this->createMockWidget();
        $this->widget->id = ProductsApiTESTService::WIDGET_ID;
        $this->widgetInfoService = app(WidgetInfoServiceInterface::class);

    }

    /**
     * @covers \App\Services\WidgetInfoService::getSortedCovers
     * @return void
     */
    public function testGetSortedCovers(): void
    {
        $this->widget->covers = self::UNSORTED_COVERS_DATA;
        $sortedCovers = $this->widgetInfoService->getSortedCovers($this->widget);
        $prevId = -1;
        foreach ($sortedCovers as $cover) {
            self::assertGreaterThan($prevId, $cover['id']);
            $prevId = (int) $cover['id'];
        }

    }

    /**
     * @covers \App\Services\WidgetInfoService::getRules
     * @return void
     */
    public function testGetRules(): void
    {
        $this->widget->rules_text = self::RULES_TEXT;
        $rules = $this->widgetInfoService->getRules($this->widget);
        self::assertStringContainsString('<br>', $rules);
    }

    /**
     * @covers \App\Services\WidgetInfoService::getLimits
     * @return void
     */
    public function testGetLimits(): void
    {
        $limits = $this->widgetInfoService->getLimits($this->widget);
        self::assertEmpty($limits);
    }


    /**
     * @covers \App\Services\WidgetInfoService::getAmounts
     * @return array
     */
    public function testGetAmounts(): array
    {
        $this->widget
            ->shouldReceive('getProductsAsArray')
            ->andReturn([ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000]);

        $amounts = $this->widgetInfoService->getAmounts($this->widget);

        self::assertCount(2, $amounts);
        $firstAmount = $amounts[0];
        $lastAmount = $amounts[1];
        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_1000, $firstAmount['id']);
        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_3000, $lastAmount['id']);

        return $amounts;
    }

    /**
     * @covers  \App\Services\WidgetInfoService::addSaleLabelToAmounts
     * @depends testGetAmounts
     * @param array $amounts
     * @return void
     */
    public function testAddSaleLabelToAmounts(array $amounts): void
    {
        $saleLabel = new WidgetSaleLabelObj(
            'sale.png',
            '#cecece',
            [ProductsApiTESTService::PRODUCT_ID_1000]
        );
        $this->widget->sale_label = $saleLabel;

        $updatedAmounts = $this->widgetInfoService->addSaleLabelToAmounts($this->widget, $amounts);
        $firstAmount = $updatedAmounts[0];
        $lastAmount = $updatedAmounts[1];

        self::assertNotNull($firstAmount['saleImage']);
        self::assertNotNull($firstAmount['saleColor']);
        self::assertNull($lastAmount['saleImage']);
        self::assertNull($lastAmount['saleColor']);
    }

}
