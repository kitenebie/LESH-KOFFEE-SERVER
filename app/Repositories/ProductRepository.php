<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(?int $categoryId = null)
    {
        $query = Product::with(['category', 'customization']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->get();
    }

    public function getById(int $id)
    {
        return Product::with(['category', 'customization'])->findOrFail($id);
    }

    public function getByCategory(int $categoryId)
    {
        return Product::with(['category', 'customization'])
            ->where('category_id', $categoryId)
            ->get();
    }

    public function getPopular()
    {
        return Product::with(['category', 'customization'])
            ->where('is_popular', true)
            ->get();
    }
}
