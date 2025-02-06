<?php

namespace Unit;

use App\Contracts\Services\CommitOrderPCDtoFactoryInterface;
use App\DTO\CommitOrderPCDto;
use App\DTO\ProductDto;
use Tests\ForTest\ProductsApiTESTService;
use Tests\TestCase;

class CommitOrderPCDtoFactoryTest extends TestCase
{
    private CommitOrderPCDtoFactoryInterface $commitOrderPCDtoFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commitOrderPCDtoFactory = app(CommitOrderPCDtoFactoryInterface::class);
    }

    /**
     * @covers \App\Services\CommitOrderPCDtoFactory::createDtoFromOrderItem
     * @return void
     */
    public function testCreateDtoFromOrderItem(): void
    {
        $orderItem = $this->createMockOrderItem();
        $orderItem->product_id = ProductsApiTESTService::PRODUCT_ID_1000;
        $commitOrderDto = $this->commitOrderPCDtoFactory->createDtoFromOrderItem($orderItem);
        $phoneNumber = str_replace('+', '', $orderItem->recipient_msisdn);

        self::assertInstanceOf(CommitOrderPCDto::class, $commitOrderDto);
        self::assertNotEmpty($commitOrderDto->orderNumber);
        self::assertIsInt($commitOrderDto->orderNumber);
        self::assertEquals($orderItem->quantity, $commitOrderDto->quantity);
        self::assertEquals($orderItem->recipient_email, $commitOrderDto->email);
        self::assertEquals($phoneNumber, $commitOrderDto->phoneNumber);
        self::assertInstanceOf(ProductDto::class, $commitOrderDto->productDto);
        self::assertNull($commitOrderDto->additionalInfo);
        self::assertNull($commitOrderDto->cardPaymentAmount);

        $productDto = $commitOrderDto->productDto;
        self::assertEquals(ProductsApiTESTService::PRODUCT_ID_1000, $productDto->id);
    }

}
