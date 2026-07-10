<?php

namespace App\Repositories\Interfaces;

interface OrderRepositoryInterface
{
    public function getByUser(int $userId);

    public function getById(int $id);

    public function create(array $data);
}
