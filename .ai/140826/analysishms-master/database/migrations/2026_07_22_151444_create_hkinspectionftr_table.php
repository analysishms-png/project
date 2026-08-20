<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hkinspectionftr', function (Blueprint $table) {
            $table->increments('sn');
            $table->integer('propertyid');
            $table->integer('inspectionid');                    // FK → hkinspectionhdr.inspectionid
            $table->integer('checklistsn')->default(0);         // FK → hkchecklistmast.sn
            $table->string('item', 100)->nullable();            // checklist item name (snapshot)
            $table->string('status', 10)->nullable();           // Pass / Fail / NA
            $table->string('remarks', 200)->nullable();
            $table->string('photopath', 255)->nullable();       // per-item photo
            $table->string('u_name', 50)->nullable();
            $table->dateTime('u_entdt')->nullable();
            $table->char('u_ae', 1)->default('a');

            $table->index(['propertyid', 'inspectionid'], 'idx_hkinsftr_insid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hkinspectionftr');
    }
};
