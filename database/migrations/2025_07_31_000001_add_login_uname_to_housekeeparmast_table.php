<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('housekeeparmast', function (Blueprint $table) {
            // Housekeeper ka login username (users.u_name se match karega)
            $table->string('login_uname', 50)->nullable()->after('u_name');
        });
    }

    public function down(): void
    {
        Schema::table('housekeeparmast', function (Blueprint $table) {
            $table->dropColumn('login_uname');
        });
    }
};
