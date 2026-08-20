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
        Schema::create('support_notification_sound_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->enum('sound_type', ['default', 'url', 'upload'])->default('default');
            $table->text('sound_url')->nullable();
            $table->string('sound_path')->nullable();
            $table->timestamps();

            $table->index('sound_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_notification_sound_settings');
    }
};
