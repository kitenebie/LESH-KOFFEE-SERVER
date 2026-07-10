<?php

namespace App\Repositories;

use App\Models\Store;
use App\Repositories\Interfaces\StoreRepositoryInterface;

class StoreRepository implements StoreRepositoryInterface
{
    public function getInfo()
    {
        return Store::with('spotlightCustomer')->first();
    }
}
