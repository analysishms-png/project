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
            $table->unsignedBigInteger('assigned_to_id')->nullable()->after('working_by_at');
            $table->string('assigned_to_name')->nullable()->after('assigned_to_id');
            $table->timestamp('assigned_at')->nullable()->after('assigned_to_name');
            $table->boolean('is_notified')->default(false)->after('assigned_at');
            $table->boolean('is_seen')->default(false)->after('is_notified');
            $table->enum('assignment_status', ['queued', 'assigned', 'accepted', 'transferred'])->default('queued')->after('is_seen');
            
            $table->index('assigned_to_id');
            $table->index('assignment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['assigned_to_id', 'assigned_to_name', 'assigned_at', 'is_notified', 'is_seen', 'assignment_status']);
        });
    }
};
