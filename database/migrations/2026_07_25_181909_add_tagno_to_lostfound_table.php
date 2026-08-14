<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add tagno column (varchar 30) after vno
        if (!Schema::hasColumn('lostfound', 'tagno')) {
            Schema::table('lostfound', function (Blueprint $table) {
                $table->string('tagno', 30)->nullable()->after('vno')->index();
            });
        }

        // 2. Extend itemcondition enum to include all condition values
        DB::statement("ALTER TABLE lostfound MODIFY COLUMN itemcondition
            ENUM('Excellent','Good','Fair','Damaged','New','Used','Broken','Scratched',
                 'Cracked','Torn','Dirty','Wet','Stained','Empty') NULL");

        // 3. Extend foundlocation to varchar(50)
        DB::statement("ALTER TABLE lostfound MODIFY COLUMN foundlocation VARCHAR(50) NULL");
    }

    public function down(): void
    {
        if (Schema::hasColumn('lostfound', 'tagno')) {
            Schema::table('lostfound', function (Blueprint $table) {
                $table->dropColumn('tagno');
            });
        }

        DB::statement("ALTER TABLE lostfound MODIFY COLUMN itemcondition
            ENUM('Excellent','Good','Fair','Damaged') NULL");

        DB::statement("ALTER TABLE lostfound MODIFY COLUMN foundlocation VARCHAR(15) NULL");
    }
};
