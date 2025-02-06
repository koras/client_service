<?php

namespace Unit;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Services\PaymentServiceInterface;
use App\DTO\CreatePaymentDataDto;
use App\DTO\RequestHostDto;
use App\Repositories\OrderRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Tests\ForTest\PaymentTESTService;
use Tests\ForTest\ProductsApiTESTService;
use Tests\TestCase;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Common\Exceptions\BadApiRequestException;
use YooKassa\Common\Exceptions\ExtensionNotFoundException;
use YooKassa\Common\Exceptions\ForbiddenException;
use YooKassa\Common\Exceptions\InternalServerError;
use YooKassa\Common\Exceptions\NotFoundException;
use YooKassa\Common\Exceptions\ResponseProcessingException;
use YooKassa\Common\Exceptions\TooManyRequestsException;
use YooKassa\Common\Exceptions\UnauthorizedException;
use YooKassa\Model\Payment\PaymentInterface;
use YooKassa\Request\Payments\CreatePaymentResponse;

class PaymentServiceTest extends TestCase
{

    private PaymentServiceInterface $paymentService;
    private OrderRepositoryInterface $orderRepository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->orderRepository = $this->createMock(OrderRepository::class);
        App::instance(OrderRepositoryInterface::class, $this->orderRepository);
        $this->paymentService = app(PaymentServiceInterface::class);

    }

    /**
     * @covers \App\Services\PaymentService::createPayment
     *
     * @return void
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function testCreatePayment(): void
    {
        $order = $this->createMockOrder();
        $orderItem = $this->createMockOrderItem();
        $collection = new Collection();
        $collection->add($orderItem);
        $order->orderItems = $collection;

        $order
            ->shouldReceive('getTotalSum')
            ->andReturn(5000);

        $requestHostDto = new RequestHostDto('httpHost', 'host');
        $createPaymentDto = new CreatePaymentDataDto($order, $requestHostDto);
        $paymentResponse =$this->paymentService->createPayment($createPaymentDto);

        self::assertInstanceOf(CreatePaymentResponse::class, $paymentResponse);
    }

    /**
     * @covers \App\Services\PaymentService::getCallbackObjectFromRequest
     * @return PaymentInterface
     * @throws ApiException
     */
    public function testGetCallbackObjectFromRequest(): PaymentInterface
    {
        $request = new Request();
        $callbackObj = $this->paymentService->getCallbackObjectFromRequest($request);

        self::assertInstanceOf(PaymentInterface::class, $callbackObj);

        return $callbackObj;
    }

    /**
     * @covers  \App\Services\PaymentService::processCallback
     * @depends testGetCallbackObjectFromRequest
     * @param PaymentInterface $callbackObj
     * @return void
     * @throws Exception
     */
    public function testProcessCallback(PaymentInterface $callbackObj): void
    {
        $order = $this->createMockOrder();
        $order->shouldReceive('wasReceivedCallback')
            ->andReturnFalse();

        $orderItem = $this->createMockOrderItem();
        $orderItem->product_id = ProductsApiTESTService::PRODUCT_ID_1000;
        $collection = new Collection();
        $collection->add($orderItem);
        $order->orderItems = $collection;
        $order->id = PaymentTESTService::PAYMENT_ORDER_ID;
        $this->orderRepository
            ->expects($this->once())
            ->method('find')
            ->with(PaymentTESTService::PAYMENT_ORDER_ID)
            ->willReturn($order);

        $result = $this->paymentService->processCallback($callbackObj);

        self::assertTrue($result);
    }

    /**
     * Тест - повторный callback от ykassa.
     * Если Ykassa выполнила повторный callback - на нашей стороне не выполняется ни каких действий
     *
     * @covers  \App\Services\PaymentService::processCallback
     * @depends testGetCallbackObjectFromRequest
     * @param PaymentInterface $callbackObj
     * @return void
     * @throws Exception
     */
    public function testRepeatProcessCallback(PaymentInterface $callbackObj): void
    {
        $order = $this->createMockOrder();
        $order->shouldReceive('wasReceivedCallback')
            ->andReturnTrue();

        $orderItem = $this->createMockOrderItem();
        $orderItem->product_id = ProductsApiTESTService::PRODUCT_ID_1000;
        $collection = new Collection();
        $collection->add($orderItem);
        $order->orderItems = $collection;
        $order->id = PaymentTESTService::PAYMENT_ORDER_ID;
        $this->orderRepository
            ->expects($this->once())
            ->method('find')
            ->with(PaymentTESTService::PAYMENT_ORDER_ID)
            ->willReturn($order);

        $result = $this->paymentService->processCallback($callbackObj);

        self::assertFalse($result);
    }

    /**
     * Проверка текущего статуса платежа во внешнем сервисе Платежей
     *
     * @covers  \App\Services\PaymentService::isPaymentSuccessInExternalService
     *
     * @return void
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function testIsStatusSucceededFromExternal()
    {
        $request = new Request();
        $callbackObj = $this->paymentService->getCallbackObjectFromRequest($request);
        $callbackObj->id = PaymentTESTService::SUCCEEDED_PAYMENT_ID;
        $isSucceeded = $this->paymentService->isPaymentSuccessInExternalService($callbackObj);
        self::assertTrue($isSucceeded);
    }

    /**
     * Проверка текущего статуса платежа во внешнем сервисе Платежей
     *
     * @covers  \App\Services\PaymentService::isPaymentSuccessInExternalService
     *
     * @return void
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function testIsStatusSucceededFromExternalFail()
    {
        $request = new Request();
        $callbackObj = $this->paymentService->getCallbackObjectFromRequest($request);
        $callbackObj->id = PaymentTESTService::CANCELED_PAYMENT_ID;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Payment status not Succeeded from PaymentInfo for Payment: ' . $callbackObj->getId());

        $isSucceeded = $this->paymentService->isPaymentSuccessInExternalService($callbackObj);
    }



}
