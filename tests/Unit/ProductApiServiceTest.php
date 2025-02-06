<?php

namespace Unit;

use App\Contracts\Services\ProductsApiServiceInterface;
use App\DTO\ProductDto;
use Illuminate\Support\Collection;
use Tests\ForTest\ProductsApiTESTService;
use Tests\TestCase;

class ProductApiServiceTest extends TestCase
{
    private ProductsApiServiceInterface $productsApiService;
    protected function setUp(): void
    {
        parent::setUp();
        $this->productsApiService = app(ProductsApiServiceInterface::class);
    }

    /**
     * @covers \Tests\ForTest\ProductsApiTESTService::getProductDataById
     * @return void
     */
    public function testGetProductDataById(): void
    {
        $productId = ProductsApiTESTService::PRODUCT_ID_1000;
        $product = $this->productsApiService->getProductDataById($productId);

        self::assertInstanceOf(ProductDto::class, $product);
    }

    /**
     * @covers \Tests\ForTest\ProductsApiTESTService::getProductsDataByIds
     * @return void
     */
    public function testGetProductsByIds(): void
    {
        $productIds = [ProductsApiTESTService::PRODUCT_ID_1000, ProductsApiTESTService::PRODUCT_ID_3000];
        $products = $this->productsApiService->getProductsDataByIds($productIds);

        self::assertInstanceOf(Collection::class, $products);
        foreach ($products as $product) {
            self::assertInstanceOf(ProductDto::class, $product);

        }
    }

}
