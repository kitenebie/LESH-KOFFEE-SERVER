<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $categoryId = $request->query('category_id');

        $products = $this->productService->getAllProducts(
            $categoryId ? (int) $categoryId : null
        );

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getProduct($id);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }
}
