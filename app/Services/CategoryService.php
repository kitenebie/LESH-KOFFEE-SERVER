<?php

namespace App\Services;

use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getAllCategories()
    {
        return $this->categoryRepository->getAll();
    }
}
