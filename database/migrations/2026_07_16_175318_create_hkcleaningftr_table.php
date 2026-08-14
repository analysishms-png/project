<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hkcleaningftr', function (Blueprint $table) {
            $table->increments('sn');                            // INT(100) AUTO_INCREMENT PK — same as hkchecklistmast
            $table->integer('propertyid');
            $table->integer('assid');                            // FK → hkcleaninghdr.cleaningid
            $table->integer('sno')->default(0);                  // line serial number
            $table->date('date')->nullable();
            $table->string('item', 50)->nullable();              // item name
            $table->string('ctype', 20)->nullable();             // checklist / Linen / Amenities / Chemical
            $table->tinyInteger('complete')->default(0);         // 0 = No, 1 = Yes
            $table->decimal('qty', 10, 2)->nullable();           // quantity (amenities)
            $table->time('time')->nullable();
            $table->string('u_name', 15)->nullable();
            $table->dateTime('u_entdt')->nullable();
            $table->char('u_ae', 1)->nullable();                 // a = add, e = edit

            $table->index(['propertyid', 'assid'], 'idx_hkftr_assid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hkcleaningftr');
    }
};
