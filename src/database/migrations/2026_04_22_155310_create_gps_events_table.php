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

        Schema::create('gps_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('gps_device_id')->constrained()->onDelete('cascade');

            // Tipo de evento
            $table->enum('event_type', [
                'engine_start',
                'engine_stop',
                'motion_start',
                'motion_stop',
                'speeding',
                'harsh_acceleration',
                'harsh_braking',
                'harsh_turn',
                'geofence_enter',
                'geofence_exit',
                'low_battery',
                'offline',
                'online',
                'ignition_on',
                'ignition_off',
                'door_open',
                'door_close',
                'collision',
                'unauthorized_movement',
                'custom'
            ]);

            $table->string('event_name');
            $table->text('description')->nullable();
            $table->json('event_data')->nullable(); // Datos del evento

            // Ubicación del evento
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Severidad
            $table->enum('severity', ['info', 'warning', 'danger', 'critical'])->default('info');

            // Estado
            $table->enum('status', ['new', 'acknowledged', 'resolved'])->default('new');
            $table->timestamp('acknowledged_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('event_type');
            $table->index('severity');
            $table->index('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gps_events');
    }
};
