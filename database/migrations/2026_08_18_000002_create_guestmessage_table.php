<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guestmessage', function (Blueprint $table) {
            $table->id();
            $table->string('propertyid', 20)->default('');
            $table->string('roomno', 20)->default('');
            $table->string('roomcat', 50)->default('');
            $table->string('caller', 100)->default('');
            $table->string('telephone', 30)->default('');
            $table->text('message')->default('');
            $table->date('recddate');
            $table->string('recdtime', 10)->default('');
            $table->string('guestprof', 50)->default('');
            $table->string('folionodocid', 50)->default('');
            $table->string('status', 20)->default('Pending');
            $table->string('deliveredby', 50)->default('');
            $table->string('u_name', 50)->default('');
            $table->timestamp('u_entdt')->nullable();
            $table->string('u_ae', 1)->default('A');
            $table->timestamps();

            $table->index('propertyid');
            $table->index('roomno');
            $table->index('recddate');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guestmessage');
    }
};
