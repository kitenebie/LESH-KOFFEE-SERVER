<?php

namespace App\Services;

use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getAllProducts(?int $categoryId = null)
    {
        return $this->productRepository->getAll($categoryId);
    }

    public function getProduct(int $id)
    {
        return $this->productRepository->getById($id);
    }

    public function getProductsByCategory(int $categoryId)
    {
        return $this->productRepository->getByCategory($categoryId);
    }

    public function getPopularProducts()
    {
        return $this->productRepository->getPopular();
    }
}
