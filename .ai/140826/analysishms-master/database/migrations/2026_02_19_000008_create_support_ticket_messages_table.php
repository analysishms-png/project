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
        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_ticket_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('sender_name')->nullable();
            $table->enum('sender_role', ['support', 'user']);
            $table->longText('message')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('support_ticket_id');
            $table->index('sender_role');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
    }
};
