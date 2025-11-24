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
        Schema::create('shop_bookers_crm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_booker_id')->unique()->constrained('shop_bookers')->onDelete('cascade');
            $table->string('name_kana')->nullable();
            $table->text('shop_memo')->nullable();
            $table->timestamp('last_booking_at')->nullable();
            $table->integer('booking_count')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_bookers_crm');
    }
};
