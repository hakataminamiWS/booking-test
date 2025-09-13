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
        Schema::create('staff_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->foreignId('staff_user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('workable_start_at');
            $table->dateTime('workable_end_at');
            $table->timestamps();

            $table->unique(['staff_user_id', 'workable_start_at', 'workable_end_at']);
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

        Schema::dropIfExists('staff_schedules');
    }
};
