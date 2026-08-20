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
        Schema::table('enviro_form', function (Blueprint $table) {
            $table->string('displayplanonprint', 3)->nullable()->default('Yes')->after('fssaicode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enviro_form', function (Blueprint $table) {
            $table->dropColumn('displayplanonprint');
        });
    }
};
