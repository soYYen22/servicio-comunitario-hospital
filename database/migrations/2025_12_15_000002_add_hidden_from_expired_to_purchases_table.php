<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('purchases', 'hidden_from_expired')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->boolean('hidden_from_expired')->default(false)->after('lote');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('purchases', 'hidden_from_expired')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('hidden_from_expired');
            });
        }
    }
};
