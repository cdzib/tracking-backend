<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['seat_num', 'qr_code']);
            $table->json('seats')->after('passenger_id'); // [{"seat":1, "qr":"uuid-1"}, ...]
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('seat_num')->after('passenger_id');
            $table->string('qr_code')->nullable()->after('status');
            $table->dropColumn('seats');
        });
    }
};
