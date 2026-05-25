<?php

namespace App\Http\Controllers;

use App\Actions\PlaceOrderAction;
use App\Exceptions\InsufficientStockException;
use App\Http\Requests\PlaceOrderRequest;
use Illuminate\Http\JsonResponse;
use App\Services\Payment\PaymentManager;
use App\Services\Analytics\ProductAnalyticsService;
use App\Models\Product;

class OrderController extends Controller
{
    public function store(PlaceOrderRequest $request, PlaceOrderAction $placeOrder): JsonResponse
    {
        try {
            $order = $placeOrder->execute(
                $request->user(),
                $request->validated('items'),
            );
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'product_id' => $e->productId,
                'requested' => $e->requested,
                'available' => $e->available,
            ], 422);
        }

        return response()->json($order, 201);
    }

    public function payment(PaymentManager $payment): JsonResponse
    {
        $payment->driver('paypal')->charge(100);
        return response()->json(['result' => 'Ok'], 200);
    }

    public function showProduct(Product $product, ProductAnalyticsService $service): JsonResponse
    {
        return response()->json([$product->id => $service->trackView($product->id)]);
    }
    
    public function showTopProducts(ProductAnalyticsService $service, ?int $limit = 5): JsonResponse
    {
        return response()->json($service->getTopViewed($limit));
    }
}
