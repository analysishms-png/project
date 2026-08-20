<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if the table exists
        if (Schema::hasTable('bom')) {
            // Drop the old primary key and add a new one that includes RawItem
            DB::statement('ALTER TABLE bom DROP PRIMARY KEY');
            DB::statement('ALTER TABLE bom ADD PRIMARY KEY (propertyid, FinItem, RawItem)');
        }
    }

    public function down()
    {
        if (Schema::hasTable('bom')) {
            DB::statement('ALTER TABLE bom DROP PRIMARY KEY');
            DB::statement('ALTER TABLE bom ADD PRIMARY KEY (propertyid, FinItem)');
        }
    }
};
