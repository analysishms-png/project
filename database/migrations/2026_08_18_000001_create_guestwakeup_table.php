<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guestwakeup', function (Blueprint $table) {
            $table->id();
            $table->string('propertyid', 20)->default('');
            $table->string('docid', 50)->default('');
            $table->integer('vno')->default(0);
            $table->string('roomno', 20)->default('');
            $table->string('roomcat', 50)->default('');
            $table->string('extension', 20)->default('');
            $table->string('remreqd', 10)->default('N');
            $table->string('foodord', 10)->default('N');
            $table->string('otherreq', 255)->default('');
            $table->date('wdate');
            $table->string('wtime', 10)->default('');
            $table->string('guestprof', 50)->default('');
            $table->string('folionodocid', 50)->default('');
            $table->string('u_name', 50)->default('');
            $table->timestamp('u_entdt')->nullable();
            $table->string('u_ae', 1)->default('A');
            $table->timestamps();

            $table->index('propertyid');
            $table->index('wdate');
            $table->index('roomno');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guestwakeup');
    }
};
