<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_saved_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('propertyid')->index();
            $table->string('report_name', 100);
            $table->text('description')->nullable();
            $table->text('config_json');
            $table->tinyInteger('is_scheduled')->default(0);
            $table->string('schedule_frequency', 20)->nullable();
            $table->string('schedule_email', 150)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['is_scheduled', 'schedule_frequency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_saved_reports');
    }
};
