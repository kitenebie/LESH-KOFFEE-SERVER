<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PromoService;
use Illuminate\Http\JsonResponse;

class PromoController extends Controller
{
    public function __construct(
        protected PromoService $promoService
    ) {}

    public function index(): JsonResponse
    {
        $promos = $this->promoService->getActivePromos();

        return response()->json([
            'success' => true,
            'data' => $promos,
        ]);
    }
}
