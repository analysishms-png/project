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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('name');
            $table->string('mobile_number', 15);
            $table->longText('problem');
            $table->enum('status', ['pending', 'working', 'complete'])->default('pending');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('property_id')->nullable();
            $table->timestamps();
            
            $table->index('ticket_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
