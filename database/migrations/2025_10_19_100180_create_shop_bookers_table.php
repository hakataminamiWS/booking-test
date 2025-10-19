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
        Schema::create('shop_bookers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canonical_id')->nullable()->constrained('shop_bookers')->onDelete('cascade');
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->char('public_id', 26)->unique();
            $table->string('nickname');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->text('note_from_booker')->nullable();
            $table->text('shop_memo')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_bookers');
    }
};
