<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unique();
            $table->string('name');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('bookers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('booking_cancellation_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_id')->unique();
            $table->string('token')->unique();
            $table->dateTime('expires_at');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('booking_option', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_id');
            $table->integer('option_id');
        });

        Schema::create('booking_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_id');
            $table->string('status');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->integer('booker_id')->nullable();
            $table->integer('menu_id')->nullable();
            $table->string('menu_name');
            $table->integer('menu_price');
            $table->integer('menu_duration');
            $table->integer('requested_staff_id')->nullable();
            $table->string('requested_staff_name')->nullable();
            $table->integer('assigned_staff_id')->nullable();
            $table->string('assigned_staff_name')->nullable();
            $table->dateTime('start_at');
            $table->string('name');
            $table->string('email');
            $table->string('tel');
            $table->text('memo')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('type');
            $table->string('timezone');
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('contract_applications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('customer_name');
            $table->string('status')->default('pending');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('email')->nullable();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('application_id')->nullable();
            $table->integer('max_shops')->default(1);
            $table->string('status');
            $table->date('start_date');
            $table->date('end_date');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->text('name');
        });

        Schema::create('menu_option', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('menu_id');
            $table->integer('option_id');
        });

        Schema::create('menu_staff', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('menu_id');
            $table->integer('user_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['menu_id', 'user_id']);
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->string('name');
            $table->integer('price');
            $table->text('description')->nullable();
            $table->integer('duration');
            $table->integer('booking_deadline_minutes')->nullable();
            $table->boolean('requires_staff_assignment')->default(true);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->string('name');
            $table->integer('price');
            $table->integer('additional_duration');
            $table->text('description')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('owners', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('name');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('user_id')->nullable()->index();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('shop_regular_holidays', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->integer('day_of_week');
            $table->boolean('is_closed')->default(true);
        });

        Schema::create('shop_specific_holidays', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->date('date');
            $table->text('name')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('shop_staff', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->integer('user_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['shop_id', 'user_id']);
        });

        Schema::create('shop_staff_shift_constraints', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->integer('day_of_week');
            $table->time('min_time')->nullable();
            $table->time('max_time')->nullable();
            $table->boolean('is_unavailable')->default(false);
        });

        Schema::create('shops', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_user_id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->integer('time_slot_interval')->default(30);
            $table->integer('cancellation_deadline_minutes')->default(1440);
            $table->integer('booking_deadline_minutes')->default(0);
            $table->string('booking_confirmation_type')->default('automatic');
            $table->string('status');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('timezone')->default('Asia/Tokyo');
        });

        Schema::create('staff_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id');
            $table->integer('staff_user_id');
            $table->dateTime('workable_start_at');
            $table->dateTime('workable_end_at');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['staff_user_id', 'workable_start_at', 'workable_end_at']);
        });

        Schema::create('user_oauth_identities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('provider');
            $table->string('provider_sub');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['provider', 'provider_sub']);
        });

        Schema::create('user_shop_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('shop_id');
            $table->string('nickname');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['user_id', 'shop_id']);
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('public_id')->unique();
            $table->boolean('is_guest')->default(false);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->foreign(['user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('bookers', function (Blueprint $table) {
            $table->foreign(['user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('booking_cancellation_tokens', function (Blueprint $table) {
            $table->foreign(['booking_id'], null)->references(['id'])->on('bookings')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('booking_option', function (Blueprint $table) {
            $table->foreign(['option_id'], null)->references(['id'])->on('options')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['booking_id'], null)->references(['id'])->on('bookings')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('booking_statuses', function (Blueprint $table) {
            $table->foreign(['booking_id'], null)->references(['id'])->on('bookings')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign(['booker_id'], null)->references(['id'])->on('bookers')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['shop_id'], null)->references(['id'])->on('shops')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['menu_id'], null)->references(['id'])->on('menus')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['requested_staff_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['assigned_staff_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });

        Schema::table('contract_applications', function (Blueprint $table) {
            $table->foreign(['user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->foreign(['application_id'], null)->references(['id'])->on('contract_applications')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('menu_option', function (Blueprint $table) {
            $table->foreign(['option_id'], null)->references(['id'])->on('options')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['menu_id'], null)->references(['id'])->on('menus')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('menu_staff', function (Blueprint $table) {
            $table->foreign(['user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['menu_id'], null)->references(['id'])->on('menus')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->foreign(['shop_id'], null)->references(['id'])->on('shops')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('options', function (Blueprint $table) {
            $table->foreign(['shop_id'], null)->references(['id'])->on('shops')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('owners', function (Blueprint $table) {
            $table->foreign(['user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('shop_regular_holidays', function (Blueprint $table) {
            $table->foreign(['shop_id'], null)->references(['id'])->on('shops')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('shop_specific_holidays', function (Blueprint $table) {
            $table->foreign(['shop_id'], null)->references(['id'])->on('shops')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('shop_staff', function (Blueprint $table) {
            $table->foreign(['user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['shop_id'], null)->references(['id'])->on('shops')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('shop_staff_shift_constraints', function (Blueprint $table) {
            $table->foreign(['shop_id'], null)->references(['id'])->on('shops')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->foreign(['owner_user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->foreign(['staff_user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['shop_id'], null)->references(['id'])->on('shops')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('user_oauth_identities', function (Blueprint $table) {
            $table->foreign(['user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('user_shop_profiles', function (Blueprint $table) {
            $table->foreign(['shop_id'], null)->references(['id'])->on('shops')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');

        Schema::dropIfExists('user_shop_profiles');

        Schema::dropIfExists('user_oauth_identities');

        Schema::dropIfExists('staff_schedules');

        Schema::dropIfExists('shops');

        Schema::dropIfExists('shop_staff_shift_constraints');

        Schema::dropIfExists('shop_staff');

        Schema::dropIfExists('shop_specific_holidays');

        Schema::dropIfExists('shop_regular_holidays');

        Schema::dropIfExists('sessions');

        Schema::dropIfExists('roles');

        Schema::dropIfExists('owners');

        Schema::dropIfExists('options');

        Schema::dropIfExists('menus');

        Schema::dropIfExists('menu_staff');

        Schema::dropIfExists('menu_option');

        Schema::dropIfExists('contracts');

        Schema::dropIfExists('contract_applications');

        Schema::dropIfExists('cache_locks');

        Schema::dropIfExists('cache');

        Schema::dropIfExists('bookings');

        Schema::dropIfExists('booking_statuses');

        Schema::dropIfExists('booking_option');

        Schema::dropIfExists('booking_cancellation_tokens');

        Schema::dropIfExists('bookers');

        Schema::dropIfExists('admins');
    }
};
