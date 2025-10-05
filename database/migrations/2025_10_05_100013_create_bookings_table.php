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
            $table->foreignId('booker_id')->constrained()->onDelete('restrict');
            $table->string('type');
            $table->foreignId('menu_id')->nullable()->constrained()->onDelete('set null');
            $table->string('menu_name');
            $table->integer('menu_price');
            $table->integer('menu_duration');
            $table->foreignId('requested_staff_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('requested_staff_name')->nullable();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('assigned_staff_name')->nullable();
            $table->dateTime('start_at');
            $table->string('timezone');
            $table->string('name');
            $table->string('email');
            $table->string('tel');
            $table->text('memo')->nullable();
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
