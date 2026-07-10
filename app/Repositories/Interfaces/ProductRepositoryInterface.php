<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface
{
    public function getAll(?int $categoryId = null);

    public function getById(int $id);

    public function getByCategory(int $categoryId);

    public function getPopular();
}
