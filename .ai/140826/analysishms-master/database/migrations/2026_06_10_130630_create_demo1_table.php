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
        Schema::create('demo1', function (Blueprint $table) {
            $table->id('orderno'); 
            $table->integer('sno')->unique()->nullable(); 
            $table->text('remark')->nullable();
            $table->date('nextfollowdate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demo1');
    }
};
