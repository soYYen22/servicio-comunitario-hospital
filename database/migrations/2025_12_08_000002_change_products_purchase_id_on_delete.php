<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeProductsPurchaseIdOnDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop existing foreign key and recreate with SET NULL
            try {
                $table->dropForeign(['purchase_id']);
            } catch (\Exception $e) {
                // ignore if foreign key does not exist
            }

            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            try {
                $table->dropForeign(['purchase_id']);
            } catch (\Exception $e) {
                // ignore
            }
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
        });
    }
}
