<?php

namespace App\Repositories;

use App\Models\Promo;
use App\Repositories\Interfaces\PromoRepositoryInterface;

class PromoRepository implements PromoRepositoryInterface
{
    public function getAll()
    {
        return Promo::with('voucher')->get();
    }

    public function getActive()
    {
        return Promo::with('voucher')
            ->where('is_active', true)
            ->get();
    }
}
