<?php

namespace App\Repositories\Interfaces;

interface SubscriptionRepositoryInterface
{
    public function getAll();

    public function getById(int $id);
}
