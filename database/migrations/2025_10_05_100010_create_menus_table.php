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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('price');
            $table->text('description')->nullable();
            $table->integer('duration');
            $table->integer('booking_deadline_minutes')->nullable();
            $table->boolean('requires_staff_assignment')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
