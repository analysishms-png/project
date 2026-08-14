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
            $table->boolean('is_user_satisfied')->default(false)->after('assignment_status');
            $table->timestamp('user_satisfied_at')->nullable()->after('is_user_satisfied');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['is_user_satisfied', 'user_satisfied_at']);
        });
    }
};
