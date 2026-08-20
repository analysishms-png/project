<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enviro_inventory', function (Blueprint $table) {
            $table->string('allow_future_date_pr', 1)->nullable()->default('N')->after('roundofftype');
        });
    }

    public function down(): void
    {
        Schema::table('enviro_inventory', function (Blueprint $table) {
            $table->dropColumn('allow_future_date_pr');
        });
    }
};
