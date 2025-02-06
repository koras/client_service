<?php

namespace App\Services;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\PromoCodesRepositoryInterface;
use App\Contracts\Services\ProductsApiServiceInterface;
use App\Contracts\Services\ProductsDataServiceInterface;
use App\DTO\ProductDto;
use App\Repositories\PromoCodesRepository;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Collection;

readonly class ProductsDataService implements ProductsDataServiceInterface
{
    public function __construct(
        private ProductsApiServiceInterface $productsApiService,
        private PromoCodesRepositoryInterface $promoCodesRepository
    )
    {
    }

    /**
     * @param WidgetInterface $widget
     * @return Collection|null <ProductDto>|null
     */
    public function getAvailableProductsByWidget(WidgetInterface $widget): ?Collection
    {
        $productIds = $widget->getProductsAsArray();
        $products = $this->productsApiService->getProductsDataByIds($productIds);
        return $this->unsetUnavailableProducts($widget, $products);
    }

    /**
     * @param WidgetInterface $widget
     * @param Collection<ProductDto> $products
     * @param string $sortKey
     * @return Collection<ProductDto>
     */
    public function sortProductsByWidgetSettings(WidgetInterface $widget, Collection $products, string $sortKey = 'id'): Collection
    {
        if ($products->isEmpty()) {
            return $products;
        }

        $productIds = $widget->getProductsAsArray();
        return $products->sortBy(function (ProductDto $product) use ($productIds, $sortKey) {
            return array_search($product->$sortKey, $productIds);
        });
    }

    /**
     * @param Collection<ProductDto> $products
     * @return array
     */
    public function convertProductsToCustomArray(Collection $products): array
    {
        $array = [];
        if ($products->isEmpty()) {
            return $array;
        }

        /** @var ProductDto $product */
        foreach ($products as $product) {
            $array[$product->id] = [
                'Name' => $product->name,
                'InStock' => $product->inStock,
                'Price' => $product->price,
                'Params' => [],
            ];
        }

        return $array;
    }

    /**
     * @param Collection $products
     * @param $productPromo
     * @return array
     */
    public function convertProductsPromoToCustomArray(Collection $products, $productPromo): array
    {
        $array = [];
        if ($products->isEmpty()) {
            return $array;
        }

        /** @var ProductDto $product */
        foreach ($products as $product) {
            $array = [
                'ProductId' => $product->id,
                'Name' => $product->name,
                'Price' => $productPromo->price,
                'Nominal' => $product->nominal,
                'Currency' => $product->currency,
                'Params' => [],
            ];
        }

        return $array;
    }

    /**
     * @param WidgetInterface $widget
     * @param Collection<ProductDto> $receivedProducts
     * @return Collection<ProductDto>
     */
    private function unsetUnavailableProducts(WidgetInterface $widget, Collection $receivedProducts): Collection
    {
        $availableProductIds = $widget->getProductsAsArray();
        $sortKey = 'id';

        // Фильтрация коллекции $receivedProducts по значениям в $sortKey, которые есть в массиве $availableProductIds
        $filteredProducts = $receivedProducts->filter(function (ProductDto $product) use ($availableProductIds, $sortKey) {
            return in_array($product->$sortKey, $availableProductIds);
        });

        return $filteredProducts;
    }

    /**
     * @param $products
     * @param $promoCode
     * @return array
     */
    public function getPromoProduct($products, $promoCode): array
    {
        $currentDateTime = now();
        foreach ($products as $row) {
            if (!empty($row['product']['product_id']) && !empty($promoCode)) {
                $promoProductId = $row['product']['product_id'];
                $product = $this->promoCodesRepository->findOneBy([
                    "needProduct" => $promoProductId,
                    "code" => $promoCode,
                ]);
                if ($product) {
                    break;
                }
            }
        }

        if (empty($product)) {
            return throw new RecordsNotFoundException();
        }

        $product = $product
            ->where('start', '<=', $currentDateTime)
            ->where('finish', '>=', $currentDateTime)
            ->where('needProduct', $promoProductId)
            ->where('code', $promoCode)
            ->where('status', '=', 0)
            ->first();

        if (empty($product)) {
            return throw new RecordsNotFoundException();
        }

        $products = $this->productsApiService->getProductsDataByIds([$product->product]);
        return $this->convertProductsPromoToCustomArray($products, $product);
    }

    /**
     * @param $products
     * @param $promoCode
     * @return Collection|null
     */
    public function getDataPromoProduct($productId, $promoCode)
    {

        $currentDateTime = now();
        $product = $this->promoCodesRepository->findOneBy([
            "product" => $productId,
            "code" => $promoCode,
        ]);

        if (empty($product)) {
            return throw new RecordsNotFoundException();
        }

        $product = $product
            ->where('start', '<=', $currentDateTime)
            ->where('finish', '>=', $currentDateTime)
            ->where('status', '=', 0)
            ->first();

        if (empty($product)) {
            return throw new RecordsNotFoundException();
        }
        return $product;
    }

}
