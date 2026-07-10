<?php

namespace App\Services;

use App\Repositories\Interfaces\VoucherRepositoryInterface;

class VoucherService
{
    public function __construct(
        protected VoucherRepositoryInterface $voucherRepository
    ) {}

    public function getAllVouchers()
    {
        return $this->voucherRepository->getAll();
    }

    public function getUserVouchers(int $userId)
    {
        return $this->voucherRepository->getByUser($userId);
    }
}
