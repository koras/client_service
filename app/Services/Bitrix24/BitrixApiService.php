<?php

namespace App\Services\Bitrix24;

use App\Contracts\Services\Bitrix24\BitrixApiServiceInterface;
use App\DTO\BitrixCreateTaskDto;
use App\Enums\HttpMethodEnum;
use App\Logging\WidgetLogObject;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BitrixApiService implements BitrixApiServiceInterface
{
    private const string CREATE_TASK_URL = 'tasks.task.add/';

    private string $host;
    private Response $response;
    private string $error;

    public function __construct(
    )
    {
        $this->host = config('bitrix-api.host');
    }

    /**
     * @param BitrixCreateTaskDto $taskDto
     * @return array
     * @throws Exception
     */
    public function sendTask(BitrixCreateTaskDto $taskDto): array
    {
        $params = [
            'fields' => $taskDto->toArray()
        ];

        $result = $this->sendToApi(HttpMethodEnum::METHOD_POST, self::CREATE_TASK_URL, $params);
        if (empty($result['result'])) {
            $log = WidgetLogObject::make('Empty response data from BitrixApiService.taskCreate', 'sendSupport');
            Log::info($log->message, $log->toContext());

            throw new Exception('Empty response data from BitrixApiService.taskCreate');
        }

        return $result;
    }

    /**
     * @param HttpMethodEnum $httpMethod
     * @param string $apiMethod
     * @param array $body
     * @return array
     * @throws Exception
     */
    private function sendToApi(HttpMethodEnum $httpMethod, string $apiMethod, array $body = []): array
    {
        $method = $httpMethod->value;

        $this->response = Http::$method($this->host . $apiMethod, $body);
        $result = $this->getContext();

        if (!empty($this->error)) {
            $log = WidgetLogObject::make('BitrixApiService SendToApi Error: ' . $this->error, 'sendSupport');
            Log::info($log->message, $log->toContext());

            throw new Exception('BitrixApiService Error: ' . $this->error);
        }

        return $result;
    }


    private function getContext(): ?array
    {
        switch ($this->response->status()) {
            case 200:
                return $this->response->json();
                break;
            default:
                $this->setError();
                return [];
        }
    }

    private function setError(): void
    {
        $jsonResponse = $this->response->json();
        if (!empty($jsonResponse['ERROR_CORE'])) {
            $this->error = $jsonResponse['error_description'] . ' code: ' . $this->response->status();
            return;
        }

        $this->error = 'Ошибка BitrixApiService' . ' code: ' . $this->response->status();
    }

}
