<?php

namespace App\Services;

use App\Contracts\Services\PCApiServiceInterface;
use App\DTO\CommitOrderPCDto;
use App\DTO\FlexCommitOrderPCDto;
use App\Enums\HttpMethodEnum;
use App\Logging\WidgetLogObject;
use Error;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Класс интеграции с API ПЦ
 */
class PCApiService implements PCApiServiceInterface
{
    private const string UPDATE_TOKEN_URL = 'Account/updateToken';
    private const string GET_CATALOG_URL = 'Account/getCatalog';
    private const string GET_REMAINS_URL = 'Account/getRemain';
    private const string GET_CATEGORIES_URL = 'Account/getCategories';
    private const string GET_ORDER_INFO_URL = 'Order/getOrderInfo';
    private const string GET_CERTIFICATES_URL = 'Order/getCertificatesBarcode';
    private const string COMMIT_ORDER_URL = 'Order/commit';
    private const string FLEX_COMMIT_ORDER_URL = 'Order/flexCommit';
    private const string SEND_PIN_TO_PHONE = 'Order/sendPinToPhone';
    private const string GET_CFT_CERTIFICATE_INFO = 'Order/getCftCertificateInfo';
    private string $apiVersion;
    private string $host;
    private string $login;
    private string $password;
    private string $token;

    private Response $response;
    private string $error;

    public function __construct()
    {
        $this->setDsn();
    }

    /**
     * Получить данные по товарам для клиента $this->login
     *
     * @return array|null
     */
    public function getCatalog(): ?array
    {
        try {
            $result = $this->sendToApi(HttpMethodEnum::METHOD_GET, self::GET_CATALOG_URL);
        } catch (Exception $e) {
            $log = WidgetLogObject::make('Error sendToApi for getCatalog for login: ' . $this->login . ' Error:' . $e->getMessage(), 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        if (!empty($this->error)) {
            $log = WidgetLogObject::make($this->error . ' for getCatalog for login: ' . $this->login, 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        // Если в ответе нет данных по Товарам - ошибка
        if (empty($result['products'])) {
            $log = WidgetLogObject::make('Empty data for getCatalog for login: ' . $this->login, 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        $log = WidgetLogObject::make('Success sendToApi for getCatalog for login: ' . $this->login, 'PCApiService');
        Log::info($log->message, $log->toContext());

        return $result;
    }

    /**
     * Получить остатки товаров для клиента $this->login
     *
     * @return array|null
     */
    public function getRemains(): ?array
    {
        try {
            $result = $this->sendToApi(HttpMethodEnum::METHOD_GET, self::GET_REMAINS_URL);
        } catch (Exception $e) {
            $log = WidgetLogObject::make('Error sendToApi for getRemains for login: ' . $this->login . ' Error:' . $e->getMessage(), 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        if (!empty($this->error)) {
            $log = WidgetLogObject::make($this->error . ' for getRemains for login: ' . $this->login, 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        $log = WidgetLogObject::make('Success sendToApi for getRemains for login: ' . $this->login, 'PCApiService');
        Log::info($log->message, $log->toContext());

        return $result;
    }

    /**
     * Создать заказ в ПЦ
     *
     * @param CommitOrderPCDto $orderPCDto
     * @return array|null
     */
    public function commitOrder(CommitOrderPCDto $orderPCDto): ?array
    {
        $log = WidgetLogObject::make('Start sendToApi for commitOrder for login: ' . $this->login , 'PCApiServiceDebug');
        Log::info($log->message, $log->toContext());

        try {
            $result = $this->sendToApi(HttpMethodEnum::METHOD_POST, self::COMMIT_ORDER_URL, $orderPCDto->toArray());
        } catch (Exception $e) {
            $log = WidgetLogObject::make('Error sendToApi for commitOrder for login: ' . $this->login . ' Error:' . $e->getMessage(), 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        if (!empty($this->error)) {
            $log = WidgetLogObject::make($this->error . ' for commitOrder for login: ' . $this->login, 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        $log = WidgetLogObject::make('Success sendToApi for commitOrder for login: ' . $this->login, 'PCApiService');
        Log::info($log->message, $log->toContext());

        return $result;
    }

    /**
     * Создать заказ с гибким номиналом в ПЦ
     *
     * @param FlexCommitOrderPCDto $orderPCDto
     * @return array|null
     */
    public function flexCommitOrder(FlexCommitOrderPCDto $orderPCDto): ?array
    {
        $log = WidgetLogObject::make('Start sendToApi for FlexCommitOrder for login: ' . $this->login , 'PCApiServiceDebug');
        Log::info($log->message, $log->toContext());

        try {
            $result = $this->sendToApi(HttpMethodEnum::METHOD_POST, self::FLEX_COMMIT_ORDER_URL, $orderPCDto->toArray());
        } catch (Exception $e) {
            $log = WidgetLogObject::make('Error sendToApi for FlexCommitOrder for login: ' . $this->login . ' Error:' . $e->getMessage(), 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        if (!empty($this->error)) {
            $log = WidgetLogObject::make($this->error . ' for FlexCommitOrder for login: ' . $this->login, 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        $log = WidgetLogObject::make('Success sendToApi for commitOrder for login: ' . $this->login, 'PCApiService');
        Log::info($log->message, $log->toContext());

        return $result;
    }

    /**
     * @param $str
     * @param $output
     * @return void
     */
    private function custom_parse_str($str, &$output): void
    {
        $pairs = explode('&', $str);
        foreach ($pairs as $pair) {
            $parts = explode('=', $pair, 2);
            $key = $parts[0];
            $value = $parts[1] ?? '';
            $output[$key] = $value;
        }
    }

    /**
     * @param string $pin
     * @return array
     * @throws Exception
     */
    private function getDataSendSmsPin(string $pin): array
    {
        $this->custom_parse_str($pin, $query_params);
        if(!isset($query_params['cid']) || !isset($query_params['productId'])){
            throw new Exception('The "cid" or "productId" parameter was not found');
        }
        return [$query_params['cid'], $query_params['productId']];
    }

    /**
     * @param string $phone
     * @param string $pin
     * @return array|null
     * @throws Exception
     */
    public function sendSmsPin(string $phone, string $pin): ?array
    {
        $url = self::SEND_PIN_TO_PHONE;
        list($certId, $productId) = $this->getDataSendSmsPin($pin);
        try {
            $result = $this->sendToApi(HttpMethodEnum::METHOD_GET, $url, [
                'certId' => $certId,
                'productId' => $productId,
                'phoneNumber' => $phone
            ]);
        } catch (Exception $e) {
            $log = WidgetLogObject::make('Error sendToApi for sendSmsPin for login: ' . $this->login . ' Error:' . $e->getMessage(), 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        if (!empty($this->error)) {
            $log = WidgetLogObject::make($this->error . ' for sendSmsPin for login: ' . $this->login, 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        $log = WidgetLogObject::make('Success sendToApi for sendSmsPin for login: ' . $this->login, 'PCApiService');
        Log::info($log->message, $log->toContext());

        return $result;
    }

    /**
     * @param int $supplierId
     * @param string $serialCertificate
     * @return array|null
     */
    public function getCftCertificateInfo(int $supplierId, string $serialCertificate): ?array
    {
        $url = self::GET_CFT_CERTIFICATE_INFO;
        try {
            $result = $this->sendToApi(HttpMethodEnum::METHOD_GET, $url, [
                'supplierId' => $supplierId,
                'number' => $serialCertificate
            ]);
        } catch (Exception $e) {
            $log = WidgetLogObject::make('Error sendToApi for getCftCertificateInfo for login: ' . $this->login . ' Error:' . $e->getMessage(), 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        if (!empty($this->error)) {
            $log = WidgetLogObject::make($this->error . ' for getCftCertificateInfo for login: ' . $this->login, 'PCApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        $log = WidgetLogObject::make('Success sendToApi for getCftCertificateInfo for login: ' . $this->login, 'PCApiService');
        Log::info($log->message, $log->toContext());

        return $result;
    }

    /**
     * Установить данные для доступа к API ПЦ
     *
     * @return void
     */
    private function setDsn(): void
    {
        $apiVersion = config('pc-api.apiVersion');
        $this->host = config('pc-api.apiHost') . '/' . $apiVersion . '/';
        $this->login = config('pc-api.apiUser');
        $this->password = config('pc-api.apiPassword');
    }

    /**
     * Обновить токен для запроса в ПЦ
     *
     * @return void
     */
    private function updateToken(): void
    {
        if (!empty($this->token)) {
            return;
        }

        try {
            $response = Http::post($this->host . self::UPDATE_TOKEN_URL, [
                'UserName' => $this->login,
                'Password' => $this->password,
            ]);

        } catch (Exception|Error $e) {

            $log = WidgetLogObject::make('Error Exception updateToken for Client: ' . $this->login . ' Error:' . $e->getMessage(), 'PCApiService');
            Log::error($log->message, $log->toContext());
            return;
        }

        if ($response->status() !== 200 || empty($response->body())) {
            $log = WidgetLogObject::make('Error updateToken for Client: ' . $this->login . ' Status Response:' . $response->status(), 'PCApiService');
            Log::error($log->message, $log->toContext());
            return;
        }

        $log = WidgetLogObject::make('Success updateToken for Client: ' . $this->login, 'PCApiService');
        Log::info($log->message, $log->toContext());

        $this->token = $response->body();
    }

    /**
     * Запрос к API ПЦ
     *
     * @param HttpMethodEnum $httpMethodEnum
     * @param string $url
     * @param array $body
     * @return array|null
     */
    private function sendToApi(HttpMethodEnum $httpMethodEnum, string $url, array $body = []): ?array
    {
        $httpMethod = $httpMethodEnum->value;
        $this->updateToken();

        $log = WidgetLogObject::make('Method: ' . $url . '; Request to PC: ' . json_encode($body), 'PCApiService');
        Log::info($log->message, $log->toContext());

        $this->response = Http::withToken($this->token)
            ->timeout(120)
            ->$httpMethod(
                $this->host . $url,
                $body
            );

        $log = WidgetLogObject::make('Method: ' . $url . 'Response from PC: ' . $this->response->body(), 'PCApiService');
        Log::info($log->message, $log->toContext());

        return $this->getContext();
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
        if (!empty($jsonResponse['errorCode']) || !empty($jsonResponse['message'])) {
            $this->error = $jsonResponse['message'];
            return;
        }

        if (empty($jsonResponse)) {
            $this->error = 'Empty response from PCApi';
            return;
        }

        $this->error = 'Ошибка PCApi';
    }
}
