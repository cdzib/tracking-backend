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
        Schema::create('gps_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');

            // Identificación
            $table->string('imei')->unique();
            $table->string('device_name');
            $table->string('device_model')->default('Unknown');
            $table->string('device_brand')->default('Generic');

            // Ubicación actual
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('altitude')->nullable();
            $table->decimal('speed', 6, 2)->default(0);
            $table->integer('course')->nullable();
            $table->decimal('accuracy', 6, 2)->nullable();
            $table->timestamp('last_update')->nullable();

            // Estado del dispositivo
            $table->enum('status', [
                'active',      // Comunicando normalmente
                'idle',        // No se ha movido
                'offline',     // Sin comunicación
                'maintenance', // Mantenimiento
                'lost'         // Perdido/Robado
            ])->default('active');

            // Datos del dispositivo
            $table->integer('battery_level')->nullable();
            $table->integer('signal_strength')->nullable(); // 0-31 (GSM)
            $table->string('phone_number')->nullable();
            $table->string('sim_operator')->nullable();
            $table->decimal('voltage', 5, 2)->nullable(); // Voltaje batería

            // Configuración
            $table->integer('gps_update_interval')->default(30); // segundos
            $table->integer('report_interval')->default(60); // segundos
            $table->json('config')->nullable(); // Configuración personalizada

            // Información última sesión
            $table->timestamp('last_online')->nullable();
            $table->timestamp('first_seen')->nullable();
            $table->timestamp('last_command_sent')->nullable();
            $table->json('last_command')->nullable();

            // Estadísticas
            $table->bigInteger('total_distance')->default(0); // metros
            $table->integer('trips_count')->default(0);
            $table->integer('error_count')->default(0);

            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('status');
            $table->index('imei');
            $table->index('last_update');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gps_devices');
    }
};
