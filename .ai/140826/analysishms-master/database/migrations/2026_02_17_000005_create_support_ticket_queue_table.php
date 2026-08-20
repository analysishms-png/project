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
        Schema::create('support_ticket_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_ticket_id')->unique();
            $table->string('property_id')->nullable();
            $table->enum('queue_status', ['queued', 'assigned'])->default('queued');
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->index('queue_status');
            $table->index('assigned_to_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_queue');
    }
};
