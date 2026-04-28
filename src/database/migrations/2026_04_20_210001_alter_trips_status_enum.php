<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'assigned',
                'picking_up',
                'on_route',
                'arrived',
                'completed',
                'cancelled',
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->enum('status', ['pending',
                'assigned',
                'picking_up',
                'on_route',
                'arrived',
                'completed',
                'cancelled'])->default('pending')->change();
        });
    }
};
