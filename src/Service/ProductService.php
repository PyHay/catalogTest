<?php

namespace App\Service;

use App\Repository\ProductRepository;

final class ProductService implements ProductServiceInterface
{
    public function __construct(
        private ProductRepository $productRepository
    ) {
    }

    public function getProductsByColumn($column): array
    {
        return $this->productRepository->sortByColumn($column);
    }

    public function getProductsByFilter(){}
}