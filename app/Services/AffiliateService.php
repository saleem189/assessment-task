<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method
        $user = User::firstOrCreate(['email' => $email], ['name' => $name, 'type' => User::TYPE_AFFILIATE]);
        // dd($merchant->toArray(), $email, $name, $commissionRate, $user);

        if ($user->merchant()->where('user_id', $user->id)->exists()) {
            throw new AffiliateCreateException("User with email $email is already associated with a merchant.");
        }

        if ($user->affiliate()->where('user_id', $user->id)->exists()) {
            throw new AffiliateCreateException("User with email $email is already associated with an affiliate for this merchant.");
        }

        $discountCode = $this->apiService->createDiscountCode($merchant)['code'];
        $affiliate = $user->affiliate()->create([
            'user_id' => $user->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $discountCode,
            'merchant_id' => $merchant->id
        ]);

        Mail::to($user)->send(new AffiliateCreated($affiliate));

        return $affiliate->fresh();
    }
}
