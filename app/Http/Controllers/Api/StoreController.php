<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    public function __construct(
        protected StoreService $storeService
    ) {}

    public function index(): JsonResponse
    {
        $store = $this->storeService->getStoreInfo();

        return response()->json([
            'success' => true,
            'data' => $store,
        ]);
    }
}
