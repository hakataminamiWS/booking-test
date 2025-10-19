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
        Schema::create('shop_menu_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_menu_id')->constrained('shop_menus')->onDelete('cascade');
            $table->foreignId('shop_option_id')->constrained('shop_options')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_menu_options');
    }
};
