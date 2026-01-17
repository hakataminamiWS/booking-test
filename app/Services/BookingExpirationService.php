<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ProvisionalBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingExpirationService
{
    /**
     * 指定された店舗の期限切れ仮予約を処理する
     *
     * @param int $shopId
     * @return int 処理された件数
     */
    public function expireOverduePendingBookings(int $shopId): int
    {
        // 期限切れの provisional_bookings レコードを取得
        // expires_at < now()
        $expiredProvisionalBookings = ProvisionalBooking::where('shop_id', $shopId)
            ->where('expires_at', '<', now())
            ->get();

        if ($expiredProvisionalBookings->isEmpty()) {
            return 0;
        }

        $count = $expiredProvisionalBookings->count();
        $expiredBookingIds = $expiredProvisionalBookings->pluck('booking_id')->toArray();

        DB::transaction(function () use ($expiredBookingIds) {
            // bookings テーブルのステータスを 'expired' に更新
            Booking::whereIn('id', $expiredBookingIds)
                ->where('status', 'pending') // 念のため現在のステータスもチェック
                ->update(['status' => 'expired']);

            // provisional_bookings から物理削除
            ProvisionalBooking::whereIn('booking_id', $expiredBookingIds)->delete();
        });

        // Log::info("Expired {$count} bookings for shop ID: {$shopId}");

        return $count;
    }
}
