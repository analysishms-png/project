<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hkfloors', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('propertyid');
            $table->string('code', 10)->nullable();
            $table->string('name', 50);
            $table->string('superviser', 50)->nullable();
            $table->tinyInteger('isactive')->default(1);
            $table->string('u_name', 50)->default('');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hkfloors');
    }
};
