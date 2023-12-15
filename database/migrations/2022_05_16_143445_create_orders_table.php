<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained();
            $table->foreignId('affiliate_id')->nullable()->constrained();
            // TODO: Replace floats with the correct data types (very similar to affiliates table)
            
            /**
             * Explanation:
             * - 'float' is a floating-point representation and may introduce rounding errors, making it less suitable for precise financial calculations.
             * - 'decimal' is a fixed-point representation, allowing you to specify exact precision, making it a better choice for monetary values.
             * - This is particularly important in financial applications to avoid potential discrepancies due to rounding.
             */

            $table->decimal('subtotal', 8, 2); // Example: 8 total digits, 2 digits after the decimal point
            $table->decimal('commission_owed', 8, 2)->default(0.00); // Example: 8 total digits, 2 digits after the decimal point
            $table->string('payout_status')->default(Order::STATUS_UNPAID);
            $table->string('discount_code')->nullable();
            $table->string('external_order_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
