<?php

namespace App\Http\Controllers;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Contracts\Services\OrderServiceInterface;
use App\Contracts\Services\PaymentServiceInterface;
use App\DTO\OrderDto;
use App\DTO\RequestHostDto;
use App\Enums\ErrorsEnum;
use App\Enums\ResponseStatusEnum;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\ResendOrderToVendorRequest;
use App\Logging\WidgetLogObject;
use App\Repositories\PromoCodesRepository;
use Error;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    public function createOrder(CreateOrderRequest $request, string $id, OrderServiceInterface $orderService, WidgetRepositoryInterface $widgetRepository): JsonResponse
    {
        try {
            /** @var WidgetInterface $widget */
            $widget = $widgetRepository->find($id);
            $promoCodeProduct = $orderService->getPromoCodeByRequest($request);
            $createOrderDto = OrderDto::fromCreateRequest($request, $widget, $promoCodeProduct);
            $hostDto = RequestHostDto::fromRequest($request);

            $paymentObj = $orderService->createOrderProcess($createOrderDto, $hostDto);

            if ($this->error->exist()) {
                $log = WidgetLogObject::make('Error from createOrder: ' . $this->error->getMessage(), 'createOrder');
                Log::error($log->message, $log->toContext());

                $this->response['status'] = ResponseStatusEnum::error->name;
                $this->response['data'] = $this->error->getToArray();
                $this->response['message'] = $this->error->getMessage();
                return response()->json($this->response, $this->error->getRequestCode());
            }
        } catch (RecordsNotFoundException $e) {
            $log = WidgetLogObject::make('Widget not found: ' . $id . ' Error: ' . $e->getMessage(), 'createOrder');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::WIDGET_NOT_FOUND);
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());

        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('Error from createOrder: ' . $e->getMessage(), 'createOrder');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::CREATE_ORDER_ERROR, $e->getMessage());
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }


        $this->response['confirmation_token'] = $paymentObj->token;
        $this->response['message'] = 'success';
        return response()->json($this->response);
    }

    public function callbackYookassa(Request $request, PaymentServiceInterface $paymentService): Response
    {
        $log = WidgetLogObject::make('YookassaCallback start: ', 'YookassaCallback');
        Log::info($log->message, $log->toContext());

        $log = WidgetLogObject::make('YookassaCallback start with request: ' . json_encode($request), 'YookassaCallback');
        Log::info($log->message, $log->toContext());
        $content = $request->getContent();
        $log = WidgetLogObject::make('YookassaCallback start with request step 1 : ' . $request->getContent(), 'YookassaCallback');
        Log::info($log->message, $log->toContext());

        //$this->lifeCycle->createStatus($order->id,"samara",3, $order->widget_id);
        DB::beginTransaction();
        try {
            $callbackObj = $paymentService->getCallbackObjectFromRequest($request);
            $paymentService->isPaymentSuccessInExternalService($callbackObj);
            $result = $paymentService->processCallback($callbackObj);
            DB::commit();
        } catch (Throwable|Error $e) {
            DB::rollBack();
            $log = WidgetLogObject::make('YookassaCallback error: ' . $e->getMessage(), 'YookassaCallback');
            Log::error($log->message, $log->toContext());

            return response('not found', 404);
        }

        if ($this->error->exist()) {
            $log = WidgetLogObject::make('YookassaCallback error: ' . $this->error->getMessage(), 'YookassaCallback');
            Log::error($log->message, $log->toContext());

            return response('not found', 404);
        }

        return response('ok');
    }

    /**
     * Переотправить заказ в ПЦ
     *
     * @param ResendOrderToVendorRequest $request
     * @param OrderServiceInterface $orderService
     * @param OrderRepositoryInterface $orderRepository
     * @param string|null $orderId
     * @return JsonResponse
     */
    public function resendOrderToVendor(ResendOrderToVendorRequest $request, OrderServiceInterface $orderService, OrderRepositoryInterface $orderRepository, string $orderId = null): JsonResponse
    {
        try {
            $order = $orderRepository->find($orderId);
            $orderService->resendOrderToVendor($order);
        } catch (RecordsNotFoundException $e) {
            $log = WidgetLogObject::make('Order not found: ' . $request->orderId . ' Error: ' . $e->getMessage(), 'resendOrderToVendor');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::ORDER_NOT_FOUND);
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());

        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('Error from resendOrderToVendor for order: ' . $request->orderId . ' Error: ' . $e->getMessage(), 'resendOrderToVendor');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::CREATE_ORDER_ERROR, $e->getMessage());
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }

        if ($this->error->exist()) {
            $log = WidgetLogObject::make('Error from resendOrderToVendor for order: ' . $request->orderId . ' Error: ' . $this->error->getMessage(), 'resendOrderToVendor');
            Log::error($log->message, $log->toContext());

            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }

        return response()->json($this->response);
    }

}
