<?php

namespace App\Contracts\Services;

use App\Contracts\Models\WidgetInterface;
use App\DTO\ProductDto;
use Exception;
use Illuminate\Support\Collection;

interface ProductsApiServiceInterface
{
    /**
     * @param int $id
     * @return ProductDto|null
     */
    public function getProductDataById(int $id): ?ProductDto;

    /**
     * @param array $productIds
     * @return Collection<ProductDto>|null
     */
    public function getProductsDataByIds(array $productIds): ?Collection;

    /**
     * Запрос на ProductServiceApi - списать количество купленных товаров
     *
     * @param array $reduceData
     * @return void
     * @throws Exception
     */
    public function reduceProducts(array $reduceData): void;

    public function getProductsDataByPositionId(int $positionId): ProductDto;
}
