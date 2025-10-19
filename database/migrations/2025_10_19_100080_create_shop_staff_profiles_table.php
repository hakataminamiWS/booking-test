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
        Schema::create('shop_staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_staff_id')->unique()->constrained('shop_staffs')->onDelete('cascade');
            $table->string('nickname');
            $table->string('small_image_url')->nullable();
            $table->string('large_image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_staff_profiles');
    }
};
