<?php

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
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('merchant_id');
            // TODO: Replace me with a brief explanation of why floats aren't the correct data type, and replace with the correct data type.
            
            /**
             * Explanation:
             * - 'float' is a floating-point representation and may introduce rounding errors, making it less suitable for precise financial calculations.
             * - 'decimal' is a fixed-point representation, allowing you to specify exact precision, making it a better choice for monetary values.
             * - This is particularly important in financial applications to avoid potential discrepancies due to rounding.
             */
            $table->decimal('commission_rate', 8, 2); // Example: 8 total digits, 2 digits after the decimal point
            $table->string('discount_code');
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
        Schema::dropIfExists('affiliates');
    }
};
