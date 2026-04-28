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
        Schema::create('gps_geofences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('cascade');

            $table->string('name');
            $table->text('description')->nullable();

            // Coordenadas (centro y radio para círculo, o puntos para polígono)
            $table->decimal('center_latitude', 10, 8)->nullable();
            $table->decimal('center_longitude', 11, 8)->nullable();
            $table->integer('radius')->nullable(); // metros

            // Para polígonos
            $table->json('polygon_points')->nullable(); // array de [lat, lng]
            $table->enum('shape_type', ['circle', 'polygon'])->default('circle');

            // Configuración
            $table->boolean('notify_on_enter')->default(true);
            $table->boolean('notify_on_exit')->default(true);
            $table->enum('alert_method', ['email', 'sms', 'push', 'all'])->default('all');

            // Estado
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gps_geofences');
    }
};
