<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDiscountAndCostprice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('products', 'discount')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('discount');
            });
        }

        if (Schema::hasColumn('purchases', 'cost_price')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('cost_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('products', 'discount')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('discount')->default(0);
            });
        }

        if (!Schema::hasColumn('purchases', 'cost_price')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->decimal('cost_price')->nullable();
            });
        }
    }
}
