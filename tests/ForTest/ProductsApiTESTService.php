<?php

namespace Tests\ForTest;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Services\ProductsApiServiceInterface;
use App\DTO\ProductDto;
use Illuminate\Support\Collection;

class ProductsApiTESTService implements ProductsApiServiceInterface
{
    public const string WIDGET_ID = '5690f6ca-fc9a-4e4e-9692-67175712e7e8';
    public const string PRODUCT_ID_1000 = '1701501000';
    public const string PRODUCT_ID_3000 = '1701503000';
    public const array PRODUCTS_DATA = [
        self::PRODUCT_ID_1000 => [
            'id' => self::PRODUCT_ID_1000,
            'widgetId' => self::WIDGET_ID,
            'name' => 'Подарочный сертификат тест',
            'productId' => '170150',
            'positionId' => '1501000',
            'categoryId' => '170',
            'nominal' => 1000,
            'currency' => 'руб.',
            'price' => 1000,
            'sellingPrice' => 1000,
            'recommendedRetailPrice' => 1000,
            'inStock' => 999,
            'isAvailable' => true,
            'isLoaded' => true,
            'isDeleted' => false,
        ],
        self::PRODUCT_ID_3000 => [
            'id' => self::PRODUCT_ID_3000,
            'widgetId' => self::WIDGET_ID,
            'name' => 'Подарочный сертификат тест',
            'productId' => '170150',
            'positionId' => '1503000',
            'categoryId' => '170',
            'nominal' => 3000,
            'currency' => 'руб.',
            'price' => 3000,
            'sellingPrice' => 3000,
            'recommendedRetailPrice' => 3000,
            'inStock' => 999,
            'isAvailable' => true,
            'isLoaded' => true,
            'isDeleted' => false,
        ]
    ];

    /**
     * @param int $id
     * @return ProductDto|null
     */
    public function getProductDataById(int $id): ?ProductDto
    {
        if (!in_array($id, array_keys(self::PRODUCTS_DATA))) {
            return null;
        }

        return ProductDto::fromArray(self::PRODUCTS_DATA[$id]);
    }

    public function getProductsDataByIds(array $productIds): ?Collection
    {
        $collection = new Collection();
        foreach ($productIds as $productId) {
            if (in_array($productId, array_keys(self::PRODUCTS_DATA))) {
                $data = self::PRODUCTS_DATA[$productId];
                $product = ProductDto::fromArray($data);
                $collection->add($product);
            }
        }

        return $collection;
    }

    public function updateProductsByWidget(WidgetInterface $widget): void
    {
        // TODO: Implement updateProductsByWidget() method.
    }

    public function reduceProducts(array $reduceData): void
    {
        // TODO: Implement reduceProducts() method.
    }
}