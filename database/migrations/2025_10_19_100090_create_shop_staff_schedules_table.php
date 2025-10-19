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
        Schema::create('shop_staff_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_staff_id')->constrained('shop_staffs')->onDelete('cascade');
            $table->dateTime('workable_start_at');
            $table->dateTime('workable_end_at');
            $table->timestamps();

            $table->unique(['shop_staff_id', 'workable_start_at', 'workable_end_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_staff_schedules');
    }
};
