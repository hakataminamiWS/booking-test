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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('shop_booker_id')->nullable()->constrained('shop_bookers')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->foreignId('menu_id')->nullable()->constrained('shop_menus')->onDelete('set null');
            $table->string('menu_name');
            $table->integer('menu_price');
            $table->integer('menu_duration');
            $table->foreignId('requested_staff_id')->nullable()->constrained('shop_staffs')->onDelete('set null');
            $table->string('requested_staff_name')->nullable();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('shop_staffs')->onDelete('set null');
            $table->string('assigned_staff_name')->nullable();
            $table->string('timezone');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('booker_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->text('note_from_booker')->nullable();
            $table->text('shop_memo')->nullable();
            $table->string('booking_channel')->default('web');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
