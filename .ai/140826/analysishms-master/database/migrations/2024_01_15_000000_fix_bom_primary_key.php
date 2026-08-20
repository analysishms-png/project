<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('bom')) {
            // Drop the old composite primary key (propertyid, FinItem)
            DB::statement('ALTER TABLE bom DROP PRIMARY KEY');
            
            // Add sn as the primary key (auto-increment)
            DB::statement('ALTER TABLE bom ADD PRIMARY KEY (sn)');
            
            // Add a unique constraint on (propertyid, FinItem, RawItem) to prevent exact duplicates
            DB::statement('ALTER TABLE bom ADD UNIQUE KEY unique_recipe (propertyid, FinItem, RawItem)');
        }
    }

    public function down()
    {
        if (Schema::hasTable('bom')) {
            DB::statement('ALTER TABLE bom DROP PRIMARY KEY');
            DB::statement('ALTER TABLE bom DROP INDEX unique_recipe');
            DB::statement('ALTER TABLE bom ADD PRIMARY KEY (propertyid, FinItem)');
        }
    }
};
