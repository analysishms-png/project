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
            $table->unsignedBigInteger('transferred_by_id')->nullable()->after('assigned_to_name');
            $table->string('transferred_by_name')->nullable()->after('transferred_by_id');
            $table->text('transfer_reason')->nullable()->after('transferred_by_name');
            $table->timestamp('transferred_at')->nullable()->after('transfer_reason');

            $table->index('transferred_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['transferred_by_id']);
            $table->dropColumn(['transferred_by_id', 'transferred_by_name', 'transfer_reason', 'transferred_at']);
        });
    }
};
