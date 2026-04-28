<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips');
            $table->unsignedBigInteger('user_id');
            $table->string('user_name');
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_chat_messages');
    }
};
