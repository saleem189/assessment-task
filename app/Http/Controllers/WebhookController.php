<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|string|uuid',
            'subtotal_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'merchant_domain' => 'required|string|regex:/^(?:www\.)?[-A-Za-z0-9]+\.[A-Za-z]{2,6}$/',
            'discount_code' => 'required|string|uuid',
        ]);
        
        $data = $request->only([
            'order_id',
            'subtotal_price',
            'merchant_domain',
            'discount_code',
        ]);

        $this->orderService->processOrder($data);

        return response()->json(['message' => 'Order processed successfully'], Response::HTTP_OK);
    }
}
