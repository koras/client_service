<?php

namespace App\Contracts\Services;

use App\DTO\CreatePaymentDataDto;
use Exception;
use Illuminate\Http\Request;
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
use YooKassa\Model\Refund\RefundInterface;
use YooKassa\Request\Payments\AbstractPaymentResponse;
use YooKassa\Request\Payments\CreatePaymentResponse;

interface PaymentServiceInterface
{
    /**
     * Создать платеж в Yookassa
     *
     * @param CreatePaymentDataDto $dataDto
     * @return CreatePaymentResponse
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
    public function createPayment(CreatePaymentDataDto $dataDto): AbstractPaymentResponse;

    /**
     * Проверить, что у платежа статус = Succeeded во внешнем сервисе платежей
     *
     * @param PaymentInterface $callbackObj
     * @return bool
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws Exception
     */
    public function isPaymentSuccessInExternalService(PaymentInterface $callbackObj): bool;

    /**
     * Получить объект callback из запроса от Yookassa
     *
     * @param Request $request
     * @return PaymentInterface|RefundInterface
     * @throws ApiException
     */
    public function getCallbackObjectFromRequest(Request $request): PaymentInterface|RefundInterface;

    /**
     * Обработать объект callback, полученный от Yookassa
     * @param PaymentInterface|RefundInterface $callbackObj
     * @return bool
     * @throws Exception
     */
    public function processCallback(PaymentInterface|RefundInterface $callbackObj): bool;

}
