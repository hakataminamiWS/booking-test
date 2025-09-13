<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_staff_shift_constraints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->integer('day_of_week');
            $table->time('min_time')->nullable();
            $table->time('max_time')->nullable();
            $table->boolean('is_unavailable')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! App::environment(['dev', 'staging'])) {
            throw new \Exception('This migration can only be rolled back in dev or staging environments.');
        }

        Schema::dropIfExists('shop_staff_shift_constraints');
    }
};
