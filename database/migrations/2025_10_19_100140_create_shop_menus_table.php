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
        Schema::create('shop_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('price')->default(0);
            $table->text('description')->nullable();
            $table->integer('duration')->default(0);
            $table->boolean('requires_cancellation_deadline')->default(false);
            $table->integer('cancellation_deadline_minutes')->default(0);
            $table->boolean('requires_booking_deadline')->default(false);
            $table->integer('booking_deadline_minutes')->default(0);
            $table->boolean('requires_staff_assignment')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_menus');
    }
};
