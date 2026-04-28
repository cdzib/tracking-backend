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
        Schema::create('vehicle_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('gps_device_id')->constrained()->onDelete('cascade');

            // Posición
            $table->float('latitude', 10, 8);
            $table->float('longitude', 11, 8);
            $table->integer('altitude')->nullable();

            // Movimiento
            $table->decimal('speed', 6, 2)->default(0); // km/h
            $table->integer('course')->nullable(); // 0-360 grados

            // Precisión
            $table->decimal('accuracy', 6, 2)->nullable();
            $table->decimal('hdop', 4, 1)->nullable(); // Dilución horizontal

            // Timestamp
            $table->timestamp('recorded_at')->useCurrent();

            // Metadatos del dispositivo en ese momento
            $table->integer('satellites')->nullable();
            $table->integer('battery_level')->nullable();
            $table->integer('signal_strength')->nullable();

            // Datos sin procesar
            $table->json('raw_data')->nullable();

            // Para búsquedas rápidas
            $table->index(['vehicle_id', 'recorded_at']);
            $table->index(['gps_device_id', 'recorded_at']);
            $table->index('recorded_at');

            // Para queries por área geográfica
            $table->index(['latitude', 'longitude']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_locations');
    }
};
