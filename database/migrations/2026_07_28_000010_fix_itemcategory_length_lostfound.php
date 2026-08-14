<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend itemcategory from varchar(20) to varchar(30)
        // "Cash & Financial Items" = 22 chars, "Jewellery & Valuables" = 21 chars
        DB::statement("ALTER TABLE lostfound MODIFY COLUMN itemcategory VARCHAR(30) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE lostfound MODIFY COLUMN itemcategory VARCHAR(20) NULL");
    }
};
