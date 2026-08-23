<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // IoT Devices
        Schema::create('smart_devices', function (Blueprint $table) {
            $table->id();
            $table->integer('propertyid')->index();
            $table->string('room_no', 20)->index();
            $table->string('device_type', 30); // light, ac, curtain, tv, sensor, lock, thermostat, speaker, camera, doorbell, motion, power
            $table->string('device_name', 100);
            $table->string('protocol', 20)->nullable(); // wifi, zigbee, z-wave, bluetooth, mqtt, http
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->string('firmware_version', 20)->nullable()->default('1.0');
            $table->tinyInteger('status')->default(0); // 0=off, 1=on
            $table->unsignedSmallInteger('brightness')->nullable()->default(100); // 0-100
            $table->decimal('temperature', 4, 1)->nullable()->default(24.0);
            $table->unsignedSmallInteger('power_watts')->default(0);
            $table->unsignedSmallInteger('battery_level')->nullable()->default(100);
            $table->tinyInteger('guest_accessible')->default(0);
            $table->timestamps();

            $table->index(['propertyid', 'room_no']);
            $table->index(['propertyid', 'device_type']);
        });

        // IoT Scenes
        Schema::create('smart_scenes', function (Blueprint $table) {
            $table->id();
            $table->integer('propertyid')->index();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable()->default('fa-lightbulb');
            $table->string('color', 20)->nullable()->default('#667eea');
            $table->tinyInteger('is_active')->default(0);
            $table->tinyInteger('is_guest_accessible')->default(0);
            $table->timestamps();
        });

        // Scene-Device mapping
        Schema::create('scene_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scene_id');
            $table->unsignedBigInteger('device_id');
            $table->tinyInteger('target_status')->default(1);
            $table->unsignedSmallInteger('target_brightness')->nullable();
            $table->decimal('target_temperature', 4, 1)->nullable();
            $table->timestamps();

            $table->foreign('scene_id')->references('id')->on('smart_scenes')->onDelete('cascade');
            $table->foreign('device_id')->references('id')->on('smart_devices')->onDelete('cascade');
        });

        // Device activity logs
        Schema::create('device_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('propertyid')->index();
            $table->unsignedBigInteger('device_id');
            $table->string('action', 50); // on, off, dim, set_temp, scene_*
            $table->string('value', 50)->nullable();
            $table->unsignedSmallInteger('duration_min')->nullable()->default(1);
            $table->string('performed_by', 100)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['propertyid', 'created_at']);
            $table->index(['device_id', 'created_at']);
        });

        // Device alerts
        Schema::create('device_alerts', function (Blueprint $table) {
            $table->id();
            $table->integer('propertyid')->index();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->string('room_no', 20)->nullable();
            $table->string('device_name', 100)->nullable();
            $table->string('title', 200);
            $table->text('message')->nullable();
            $table->string('severity', 20)->default('info'); // critical, warning, info
            $table->tinyInteger('is_resolved')->default(0);
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by', 100)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['propertyid', 'is_resolved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_alerts');
        Schema::dropIfExists('device_logs');
        Schema::dropIfExists('scene_devices');
        Schema::dropIfExists('smart_scenes');
        Schema::dropIfExists('smart_devices');
    }
};
