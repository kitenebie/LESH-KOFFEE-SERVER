<?php

namespace App\Services;

use App\Repositories\Interfaces\PromoRepositoryInterface;

class PromoService
{
    public function __construct(
        protected PromoRepositoryInterface $promoRepository
    ) {}

    public function getAllPromos()
    {
        return $this->promoRepository->getAll();
    }

    public function getActivePromos()
    {
        return $this->promoRepository->getActive();
    }
}
