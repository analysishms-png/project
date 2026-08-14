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
        Schema::create('support_ticket_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_ticket_id');
            $table->unsignedBigInteger('transferred_by_id')->nullable();
            $table->string('transferred_by_name')->nullable();
            $table->unsignedBigInteger('transferred_to_id')->nullable();
            $table->string('transferred_to_name')->nullable();
            $table->text('reason');
            $table->timestamps();

            $table->index('support_ticket_id');
            $table->index('transferred_by_id');
            $table->index('transferred_to_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_transfers');
    }
};
