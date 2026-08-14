<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hk_audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('propertyid', 20);
            $table->integer('user_id')->nullable();
            $table->string('user_name', 50)->nullable();
            $table->string('action', 50);
            $table->string('module', 50);
            $table->string('record_id', 50)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('description', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('u_entdt')->nullable();
            $table->timestamps();
            
            $table->index('propertyid');
            $table->index('action');
            $table->index('module');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hk_audit_log');
    }
};
