<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Staff check-in/out tracking
        Schema::create('staff_checkins', function (Blueprint $table) {
            $table->id();
            $table->integer('propertyid')->index();
            $table->string('staff_id', 50);
            $table->string('staff_name', 100)->nullable();
            $table->string('department', 50)->nullable()->default('Housekeeping');
            $table->date('check_date');
            $table->timestamp('check_in');
            $table->timestamp('check_out')->nullable();
            $table->decimal('latitude_in', 10, 7)->nullable();
            $table->decimal('longitude_in', 10, 7)->nullable();
            $table->decimal('latitude_out', 10, 7)->nullable();
            $table->decimal('longitude_out', 10, 7)->nullable();
            $table->string('u_name', 50)->nullable();
            $table->timestamp('u_entdt')->nullable();
            $table->timestamp('u_updatedt')->nullable();

            $table->index(['propertyid', 'check_date']);
            $table->index(['staff_id', 'check_date']);
        });

        // Staff task status change log
        Schema::create('staff_task_log', function (Blueprint $table) {
            $table->id();
            $table->integer('propertyid')->index();
            $table->integer('task_id');
            $table->string('task_type', 20); // cleaning, maintenance
            $table->string('status', 30);
            $table->string('staff_id', 50)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('u_name', 50)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['propertyid', 'task_type']);
            $table->index(['propertyid', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_task_log');
        Schema::dropIfExists('staff_checkins');
    }
};
