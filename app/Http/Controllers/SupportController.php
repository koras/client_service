<?php

namespace App\Http\Controllers;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\WidgetRepositoryInterface;
use App\Contracts\Services\Bitrix24\BitrixTaskDtoBuilderInterface;
use App\Contracts\Services\Bitrix24\BitrixTaskServiceInterface;
use App\Contracts\Services\QueueProducerInterface;
use App\DTO\NotificationDto;
use App\Enums\ErrorsEnum;
use App\Enums\ResponseStatusEnum;
use App\Http\Requests\SendSupportRequest;
use App\Logging\WidgetLogObject;
use Error;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class SupportController extends Controller
{

    public function sendSupport(SendSupportRequest $request, string $id, WidgetRepositoryInterface $widgetRepository, QueueProducerInterface $queueProducer, BitrixTaskServiceInterface $bitrixTaskService): JsonResponse
    {
        try {
            /** @var WidgetInterface $widget */
            $widget = $widgetRepository->find($id);
            $dto = NotificationDto::createSupportFromRequest($request, $widget);
//            $queueProducer->publishNotificationQueue($dto);

            $bitrixTaskService->createTaskFromNotificationAndWidget($dto, $widget);

        } catch (RecordsNotFoundException $e) {
            $log = WidgetLogObject::make('Widget not found: ' . $id . ' Error: ' . $e->getMessage(), 'sendSupport');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::WIDGET_NOT_FOUND);
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }  catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('Error from sendSupport: ' . $e->getMessage(), 'sendSupport');
            Log::error($log->message, $log->toContext());

            $this->error->setError(ErrorsEnum::SEND_SUPPORT_ERROR, $e->getMessage());
            $this->response['status'] = ResponseStatusEnum::error->name;
            $this->response['data'] = $this->error->getToArray();
            $this->response['message'] = $this->error->getMessage();
            return response()->json($this->response, $this->error->getRequestCode());
        }


        return response()->json($this->response);
    }

}
