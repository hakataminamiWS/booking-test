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
        Schema::table('bookings', function (Blueprint $table) {
            // 既存の外部キー制約を削除
            $table->dropForeign(['booker_id']);

            // statusカラムを削除し、typeカラムを追加
            $table->dropColumn('status');
            $table->string('type')->after('booker_id');

            // bookersテーブルへの新しい外部キー制約を追加
            $table->foreign('booker_id')
                  ->references('id')
                  ->on('bookers')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // 新しい外部キー制約を削除
            $table->dropForeign(['booker_id']);

            // typeカラムを削除し、statusカラムを復元
            $table->dropColumn('type');
            $table->string('status')->default('pending');

            // 古い外部キー制約を復元
            $table->foreign('booker_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
