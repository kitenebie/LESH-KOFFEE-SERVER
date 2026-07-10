<?php

namespace App\Services;

use App\Repositories\Interfaces\StoreRepositoryInterface;

class StoreService
{
    public function __construct(
        protected StoreRepositoryInterface $storeRepository
    ) {}

    public function getStoreInfo()
    {
        return $this->storeRepository->getInfo();
    }
}
