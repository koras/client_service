<?php

namespace App\Contracts\Services;

use App\Contracts\Models\WidgetInterface;
use App\DTO\ProductDto;
use Illuminate\Support\Collection;

interface ProductsDataServiceInterface
{
    /**
     * @param WidgetInterface $widget
     * @return Collection|null <ProductDto>|null
     */
    public function getAvailableProductsByWidget(WidgetInterface $widget): ?Collection;

    /**
     * @param WidgetInterface $widget
     * @param Collection<ProductDto> $products
     * @param string $sortKey
     * @return Collection<ProductDto>
     */
    public function sortProductsByWidgetSettings(WidgetInterface $widget, Collection $products, string $sortKey = 'id'): Collection;

    /**
     * @param Collection<ProductDto> $products
     * @return array
     */
    public function convertProductsToCustomArray(Collection $products): array;

}
