<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gps_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gps_device_id')->constrained()->onDelete('cascade');
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users');

            // Tipo de comando
            $table->enum('command_type', [
                'update_interval',
                'reboot',
                'shutdown',
                'power_save',
                'get_status',
                'get_location',
                'set_geofence',
                'remove_geofence',
                'emergency_stop',
                'custom'
            ]);

            // Parámetros del comando
            $table->json('parameters')->nullable();

            // Estado
            $table->enum('status', ['pending', 'sent', 'acknowledged', 'failed'])->default('pending');
            $table->text('response')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index('gps_device_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gps_commands');
    }
};
