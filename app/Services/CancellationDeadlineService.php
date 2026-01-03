<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\ShopMenu;
use Illuminate\Support\Carbon;

class CancellationDeadlineService
{
    /**
     * 予約のキャンセル期限を計算する
     *
     * @param Shop $shop 店舗
     * @param ShopMenu $menu メニュー
     * @param Carbon $startAt 予約開始日時
     * @return Carbon キャンセル期限日時
     */
    public function calculate(Shop $shop, ShopMenu $menu, Carbon $startAt): Carbon
    {
        // デフォルト: 店舗設定 (未設定の場合は24時間前)
        $minutes = $shop->cancellation_deadline_minutes ?? 1440;

        // 優先設定: メニュー個別設定
        if ($menu->requires_cancellation_deadline && $menu->cancellation_deadline_minutes !== null) {
            $minutes = $menu->cancellation_deadline_minutes;
        }

        return $startAt->copy()->subMinutes($minutes);
    }

    /**
     * キャンセル期限をフォーマットして返す
     *
     * @param Shop $shop 店舗
     * @param ShopMenu $menu メニュー
     * @param Carbon $startAt 予約開始日時
     * @param string $format 出力フォーマット
     * @return string フォーマット済みキャンセル期限
     */
    public function getFormattedDeadline(Shop $shop, ShopMenu $menu, Carbon $startAt, string $format = 'Y/m/d H:i'): string
    {
        return $this->calculate($shop, $menu, $startAt)->format($format);
    }
}
