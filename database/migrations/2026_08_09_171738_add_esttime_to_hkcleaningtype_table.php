<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hkcleaningtype', 'esttime')) {
            Schema::table('hkcleaningtype', function (Blueprint $table) {
                $table->string('esttime', 10)->nullable()->after('name')
                      ->comment('Estimated cleaning time e.g. 00:20:00');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hkcleaningtype', 'esttime')) {
            Schema::table('hkcleaningtype', function (Blueprint $table) {
                $table->dropColumn('esttime');
            });
        }
    }
};
