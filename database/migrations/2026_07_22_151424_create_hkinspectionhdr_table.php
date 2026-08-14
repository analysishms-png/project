<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hkinspectionhdr', function (Blueprint $table) {
            $table->increments('inspectionid');
            $table->string('insno', 30)->nullable();            // INS-YYYYMMDD-XXXXX
            $table->integer('propertyid');
            $table->integer('cleaningid')->nullable();          // FK → hkcleaninghdr.cleaningid
            $table->string('roomno', 20)->nullable();
            $table->dateTime('inspectiondate')->nullable();
            $table->string('shift', 30)->nullable();            // Morning / Afternoon / Evening
            $table->string('inspectorid', 30)->nullable();      // supervisor/inspector code
            $table->decimal('score', 5, 2)->default(0);         // auto-calculated %
            $table->tinyText('remarks')->nullable();
            $table->char('engineering_req', 1)->default('N');
            $table->char('amenities_refill', 1)->default('N');
            $table->char('lost_found', 1)->default('N');
            $table->string('result', 20)->nullable();           // Pass / Fail / Re-Cleaning
            $table->char('reclean_required', 1)->default('N');
            $table->string('priority_reclean', 20)->nullable();
            $table->string('u_name', 50)->nullable();
            $table->dateTime('u_entdt')->nullable();
            $table->dateTime('u_updatedt')->nullable();
            $table->char('u_ae', 1)->default('a');

            $table->index(['propertyid', 'cleaningid'], 'idx_hkins_cleaningid');
            $table->index(['propertyid'], 'idx_hkins_propid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hkinspectionhdr');
    }
};
