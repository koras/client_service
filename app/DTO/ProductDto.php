<?php

namespace App\DTO;

readonly class ProductDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string|int $productId,
        public int $positionId,
        public int $categoryId,
        public int $nominal,
        public string $currency,
        public float $price,
        public float $sellingPrice,
        public float $recommendedRetailPrice,
        public int $inStock,
        public bool $isAvailable,
        public bool $isLoaded,
        public bool $isDeleted
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['productId'],
            $data['positionId'],
            $data['categoryId'],
            $data['nominal'],
            $data['currency'],
            $data['price'],
            $data['sellingPrice'],
            $data['recommendedRetailPrice'],
            $data['inStock'],
            $data['isAvailable'],
            $data['isLoaded'],
            $data['isDeleted']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'productId' => $this->productId,
            'positionId' => $this->positionId,
            'categoryId' => $this->categoryId,
            'nominal' => $this->nominal,
            'currency' => $this->currency,
            'sellingPrice' => $this->sellingPrice,
            'price' => $this->price,
            'recommendedRetailPrice' => $this->recommendedRetailPrice,
            'inStock' => $this->inStock,
            'isAvailable' => $this->isAvailable,
            'isLoaded' => $this->isLoaded,
            'isDeleted' => $this->isDeleted
        ];
    }
}
