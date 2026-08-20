<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hkdamage', function (Blueprint $table) {
            $table->increments('sn');
            $table->integer('propertyid');
            $table->integer('damageid')->nullable();          // auto serial (generated in controller)
            $table->string('roomno', 20)->nullable();
            $table->date('date')->nullable();
            $table->string('damagetype', 50)->nullable();     // Furniture, Electronic, etc.
            $table->string('item', 100)->nullable();          // item name
            $table->text('description')->nullable();
            $table->string('status', 30)->default('Pending'); // Pending / In Progress / Resolved
            $table->string('u_name', 50)->nullable();
            $table->dateTime('u_entdt')->nullable();
            $table->char('u_ae', 1)->default('a');            // a=add, e=edit

            $table->index(['propertyid'], 'idx_hkdamage_prop');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hkdamage');
    }
};
