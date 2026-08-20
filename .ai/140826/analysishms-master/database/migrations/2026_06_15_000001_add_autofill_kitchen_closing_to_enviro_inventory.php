<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enviro_inventory', function (Blueprint $table) {
            $table->boolean('autofill_kitchen_closing')->default(false)->after('allow_future_date_pr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enviro_inventory', function (Blueprint $table) {
            $table->dropColumn('autofill_kitchen_closing');
        });
    }
};
