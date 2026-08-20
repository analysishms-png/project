<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enviro_banquet', function (Blueprint $table) {
            $table->tinyInteger('adv_tax_on_bill')->default(0)->after('booking_edit');
        });
    }

    public function down(): void
    {
        Schema::table('enviro_banquet', function (Blueprint $table) {
            $table->dropColumn('adv_tax_on_bill');
        });
    }
};
