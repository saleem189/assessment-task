<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        $merchant = null;
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['api_key'],
            'type' => User::TYPE_MERCHANT,
        ]);


        if($user){
            $merchant = $user->merchant()->create([
                'domain' => $data['domain'],
                'display_name' => $data['name'],
            ]);
        }

        return $merchant;
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        $updateUser = User::find($user->id);
        $updateUser->name = $data['name'];
        $updateUser->email = $data['email'];
        $updateUser->password = $data['api_key'];
        $updateUser->save();
       

        $merchantRecord = Merchant::where('user_id',$user->id)->first();
        $merchantRecord->domain = $data['domain'];
        $merchantRecord->display_name = $data['name'];
        $merchantRecord->save();
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        $user = User::where('email', $email)->first();
        return $user ? $user->merchant : null;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        $unpaidOrders = $affiliate->orders()->where('payout_status', Order::STATUS_UNPAID)->get();

        foreach ($unpaidOrders as $order) {
            dispatch(new PayoutOrderJob($order));
        }
    }

     /**
     * Get useful order statistics for the merchant API.
     *
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @return array
     */
    public function getOrderStatistics(Carbon $fromDate, Carbon $toDate): array
    {
        $orderStats = Order::whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(subtotal) as revenue')
            ->selectRaw('SUM(CASE WHEN affiliate_id IS NOT NULL AND payout_status = ? THEN commission_owed ELSE 0 END) as commission_owed', [Order::STATUS_UNPAID])
            ->first();
    
        return [
            'count' => $orderStats->count,
            'revenue' => $orderStats->revenue,
            'commissions_owed' => $orderStats->commission_owed,
        ];
    }    
}
