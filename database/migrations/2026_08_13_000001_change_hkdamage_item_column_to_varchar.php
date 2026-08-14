<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BUG-HK-008: hkdamage.item was int(35) — the damage entry form submits an item
     * NAME (string) and MySQL silently coerced it to 0, so every damage record
     * showed "0" in the Item column and the Damage Report item table was empty.
     * Change the column to varchar so the actual item name is stored.
     */
    public function up(): void
    {
        Schema::table('hkdamage', function (Blueprint $table) {
            $table->string('item', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('hkdamage', function (Blueprint $table) {
            $table->integer('item')->change();
        });
    }
};
