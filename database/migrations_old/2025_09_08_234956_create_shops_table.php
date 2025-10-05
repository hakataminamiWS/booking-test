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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->onDelete('cascade');
            $table->string('slug')->unique();
            $table->string('name');
            $table->integer('time_slot_interval')->default(30);
            $table->integer('cancellation_deadline_minutes')->default(1440);
            $table->integer('booking_deadline_minutes')->default(0);
            $table->string('booking_confirmation_type')->default('automatic');
            $table->string('status');
            $table->timestamps();
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

        Schema::dropIfExists('shops');
    }
};
