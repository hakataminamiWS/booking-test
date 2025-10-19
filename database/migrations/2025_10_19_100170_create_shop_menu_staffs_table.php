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
        Schema::create('shop_menu_staffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_menu_id')->constrained('shop_menus')->onDelete('cascade');
            $table->foreignId('shop_staff_id')->constrained('shop_staffs')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['shop_menu_id', 'shop_staff_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_menu_staffs');
    }
};
