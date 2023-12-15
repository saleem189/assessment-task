<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();
        
        $existingOrder = Order::where('external_order_id', $data['order_id'])->first();

        
        if ($existingOrder) {
            return;
        }
        
        $user = User::firstOrCreate([
            'email' => $data['customer_email'], 
        ],[
            'name' => $data['customer_name'],
            'type' => User::TYPE_AFFILIATE
        ]);

        $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 0.1);
        $affiliate = Affiliate::firstOrCreate([
            'merchant_id' => $merchant->id,
        ],[
            'user_id' => $user->id,
            'discount_code' => $data['discount_code'],
            'commission_rate' => $merchant->default_commission_rate
        ]);

        Order::create([
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
            'subtotal' => $data['subtotal_price'],
            'external_order_id' => $data['order_id'],
            'commission_owed' => $data['subtotal_price'] * $affiliate->commission_rate,
        ]);
    }
}
