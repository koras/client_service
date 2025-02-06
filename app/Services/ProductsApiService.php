<?php

namespace App\Services;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Services\ProductsApiServiceInterface;
use App\DTO\ProductDto;
use App\Enums\HttpMethodEnum;
use App\Enums\ProductApiResponseStatusEnum;
use App\Logging\WidgetLogObject;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductsApiService implements ProductsApiServiceInterface
{
    private const string GET_PRODUCTS_BY_IDS_URL = '/api/products/';
    private const string GET_PRODUCTS_BY_POSITION_ID_URL = '/api/position/';
    private const string REDUCE_PRODUCTS_URL = '/api/products/reduce/';

    private string $host;
    private Response $response;
    private string $error;


    public function __construct()
    {
        $this->host = config('products-api.host');
    }

    /**
     * @param int $id
     * @return ProductDto|null
     */
    public function getProductDataById(int $id): ?ProductDto
    {
        $params = [
            'productIds' => [$id],
            //'isAvailable' => true,
            //'isLoaded' => true,
            //'isInStock' => true
        ];

        $log = WidgetLogObject::make('Start getProductDataById for Product: ' . $id . ' $params = ' . json_encode($params), 'ProductsApiService');
        Log::error($log->message, $log->toContext());

        try {
            $result = $this->sendToApi(HttpMethodEnum::METHOD_GET->value, self::GET_PRODUCTS_BY_IDS_URL, $params);
            $product = $result[0];
            $productDto = ProductDto::fromArray($product);
        } catch (Exception $e) {
            $log = WidgetLogObject::make('Error getProductDataById for Product: ' . $id . ' Error:' . $e->getMessage(), 'ProductsApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        if (!empty($this->error)) {
            $log = WidgetLogObject::make($this->error . ' for getProductDataById for Product: ' . $id, 'ProductsApiService');
            Log::error($log->message, $log->toContext());
            return null;
        }

        $log = WidgetLogObject::make('Success sendToApi for getProductDataById for Product: ' . $id, 'ProductsApiService');
        Log::info($log->message, $log->toContext());

        return $productDto;
    }

    /**
     * @param array $productIds
     * @return Collection<ProductDto>|null
     * @throws Exception
     */
    public function getProductsDataByIds(array $productIds): ?Collection
    {
        $params = [
            'productIds' => $productIds,
            'isAvailable' => true,
            'isLoaded' => true,
            'isInStock' => true
        ];

        try {
            $result = $this->sendToApi(HttpMethodEnum::METHOD_GET->value, self::GET_PRODUCTS_BY_IDS_URL, $params);
        } catch (Exception $e) {
            $log = WidgetLogObject::make('Error getProductsDataByIds for Products: ' . json_encode($productIds) . ' Error:' . $e->getMessage(), 'ProductsApiService');
            Log::error($log->message, $log->toContext());
            throw new Exception('Error getProductsDataByIds: ' . $e->getMessage());
        }

        if (!empty($this->error)) {
            $log = WidgetLogObject::make($this->error . ' for getProductsDataByIds for Products: ' . json_encode($productIds), 'ProductsApiService');
            Log::error($log->message, $log->toContext());
            throw new Exception($this->error . ' for getProductsDataByIds for Products: ' . json_encode($productIds));

        }

        $log = WidgetLogObject::make('Success sendToApi for getProductsDataByIds for Products: ' . json_encode($productIds), 'ProductsApiService');
        Log::info($log->message, $log->toContext());

        $collection = new Collection();
        foreach ($result as $productData) {
            $productDto = ProductDto::fromArray($productData);
            $collection->add($productDto);
        }

        return $collection;
    }

    /**
     * @param int $positionId
     * @return ProductDto
     * @throws Exception
     */
    public function getProductsDataByPositionId(int $positionId): ProductDto
    {
        $params = [
            'positionId' => $positionId,
        ];

        try {
            $result = $this->sendToApi(HttpMethodEnum::METHOD_GET->value, self::GET_PRODUCTS_BY_POSITION_ID_URL.$positionId, $params);

        } catch (Exception $e) {
            $log = WidgetLogObject::make('Error getProductsDataByPositionId for Products: ' . $positionId . ' Error:' . $e->getMessage(), 'ProductsApiService');
            Log::error($log->message, $log->toContext());
            throw new Exception('Error getProductsDataByPositionId: ' . $e->getMessage());
        }

        if (!empty($this->error)) {
            $log = WidgetLogObject::make($this->error . ' for getProductsDataByIds for Products: ' . $positionId, 'ProductsApiService');
            Log::error($log->message, $log->toContext());
            throw new Exception($this->error . ' for getProductsDataByIds for Products: ' . $positionId);

        }

        $log = WidgetLogObject::make('Success sendToApi for getProductsDataByPositionId for Products: ' . $positionId, 'ProductsApiService');
        Log::info($log->message, $log->toContext());

        return ProductDto::fromArray($result);

    }

    /**
     * Запрос на ProductServiceApi - списать количество купленных товаров
     *
     * @param array $reduceData
     * @return void
     * @throws Exception
     */
    public function reduceProducts(array $reduceData): void
    {

        try {
            $result = $this->sendToApi(HttpMethodEnum::METHOD_POST->value, self::REDUCE_PRODUCTS_URL, $reduceData);
        } catch (Exception $e) {
            $log = WidgetLogObject::make('Error reduceProducts for Products: ' . json_encode($reduceData) . ' Error:' . $e->getMessage(), 'ProductsApiService');
            Log::error($log->message, $log->toContext());
            throw new Exception('Error reduceProducts: ' . $e->getMessage());
        }

        if (!empty($this->error)) {
            $log = WidgetLogObject::make($this->error . ' for reduceProducts for Products: ' . json_encode($reduceData), 'ProductsApiService');
            Log::error($log->message, $log->toContext());
            throw new Exception($this->error . ' for reduceProducts for Products: ' . json_encode($reduceData));

        }

        $log = WidgetLogObject::make('Success sendToApi for reduceProducts for Products: ' . json_encode($reduceData), 'ProductsApiService');
        Log::info($log->message, $log->toContext());
    }

    /**
     * @throws Exception
     */
    private function sendToApi(string $httpMethod, string $apiMethod, array $body = []): ?array
    {
        if (is_null(HttpMethodEnum::tryFrom($httpMethod))) {
            throw new Exception('Undefined Http Method');
        }

        // DebugLog
        $logData = [
            'request' => 'ProductsApiService::' . $apiMethod,
            'body' => json_encode($body),
        ];
        $log = WidgetLogObject::make('ProductsApiService ' . '$apiMethod:' . $apiMethod . ' Request: ' . json_encode($logData), 'ProductsApiService');
        Log::debug($log->message, $log->toContext());
        // DebugLog

        $this->response = Http::$httpMethod($this->host . $apiMethod, $body);

        // DebugLog
        $logData = [
            'response' => json_encode($this->response),
            'status' => $this->response->status(),
            'body' => json_encode($this->response->body()),
            'json' => $this->response->json()
        ];
        $log = WidgetLogObject::make('ProductsApiService ' . '$apiMethod:' . $apiMethod . ' Response: ' . json_encode($logData), 'ProductsApiService');
        Log::debug($log->message, $log->toContext());
        // DebugLog

        return $this->getContext();
    }

    private function getContext(): ?array
    {
        switch ($this->response->status()) {
            case 200:
                $jsonResponse = $this->response->json();
                return $jsonResponse['data'];
                break;
            default:
                $this->setError();
                return [];
        }
    }

    private function setError(): void
    {
        $jsonResponse = $this->response->json();
        if (!empty($jsonResponse['status']) && ProductApiResponseStatusEnum::isError($jsonResponse['status'])) {
            $this->error = $jsonResponse['message'] . ' status: ' . $this->response->status() ?? 'unknown';
            return;
        }

        if (empty($jsonResponse['data'])) {
            $this->error = 'Empty response data from ProductApiService' . ' status: ' . $this->response->status() ?? 'unknown';
            return;
        }

        $this->error = 'Ошибка ProductApiService' . ' status: ' . $this->response->status() ?? 'unknown';
    }

}
