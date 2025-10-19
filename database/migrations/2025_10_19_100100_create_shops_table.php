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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->onDelete('cascade');
            $table->string('slug')->unique();
            $table->string('name');
            $table->integer('time_slot_interval')->default(30);
            $table->integer('cancellation_deadline_minutes')->default(1440);
            $table->integer('booking_deadline_minutes')->default(0);
            $table->string('booking_confirmation_type')->default('automatic');
            $table->boolean('accepts_online_bookings')->default(true);
            $table->string('email');
            $table->string('timezone')->default('Asia/Tokyo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
