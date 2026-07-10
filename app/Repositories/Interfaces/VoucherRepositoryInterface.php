<?php

namespace App\Repositories\Interfaces;

interface VoucherRepositoryInterface
{
    public function getAll();

    public function getByUser(int $userId);
}
