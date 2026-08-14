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
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('working_by_id')->nullable()->after('property_id');
            $table->string('working_by_name')->nullable()->after('working_by_id');
            $table->timestamp('working_by_at')->nullable()->after('working_by_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['working_by_id', 'working_by_name', 'working_by_at']);
        });
    }
};
