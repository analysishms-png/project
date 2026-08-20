<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hkroomassigns', 'cleaningstatus')) {
            Schema::table('hkroomassigns', function (Blueprint $table) {
                $table->string('cleaningstatus', 30)->default('')->after('assno')
                      ->comment('empty = not started, In Progress, Completed');
            });
        } else {
            // Ensure the column is wide enough (was varchar(10) on older installs)
            Schema::table('hkroomassigns', function (Blueprint $table) {
                $table->string('cleaningstatus', 30)->default('')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hkroomassigns', 'cleaningstatus')) {
            Schema::table('hkroomassigns', function (Blueprint $table) {
                $table->dropColumn('cleaningstatus');
            });
        }
    }
};
