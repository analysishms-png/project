<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hkcleaninghdr', 'assno')) {
            Schema::table('hkcleaninghdr', function (Blueprint $table) {
                $table->unsignedInteger('assno')->nullable()->after('cleaningid')
                      ->comment('Assignment No from hkroomassigns.assno');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hkcleaninghdr', 'assno')) {
            Schema::table('hkcleaninghdr', function (Blueprint $table) {
                $table->dropColumn('assno');
            });
        }
    }
};
