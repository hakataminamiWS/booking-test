<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index($shop_id)
    {
        // ダミーのダッシュボードデータ
        $dashboardData = [
            'today_summary' => [
                'confirmed_bookings' => 5,
                'pending_bookings' => 2,
                'canceled_bookings' => 1,
                'total_revenue' => '¥25,000', // ダミー
            ],
            'upcoming_bookings' => [
                ['time' => '10:00', 'booker_name' => '田中 太郎', 'service' => 'カット', 'status' => '確定'],
                ['time' => '11:30', 'booker_name' => '鈴木 花子', 'service' => 'カラー', 'status' => '確定'],
                ['time' => '14:00', 'booker_name' => '佐藤 健', 'service' => 'パーマ', 'status' => 'ペンディング'],
            ],
            'pending_tasks' => [
                'new_booking_requests' => 2,
                'cancellation_requests' => 1,
                'change_requests' => 0,
            ],
            'quick_links' => [
                ['text' => '予約一覧', 'url' => route('staff.bookings.index', ['shop_id' => $shop_id])],
                ['text' => '新規予約追加', 'url' => route('staff.bookings.create', ['shop_id' => $shop_id])],
                // ['text' => 'スケジュール管理', 'url' => '#'], // 未実装
                // ['text' => '顧客管理', 'url' => '#'], // 未実装
            ],
        ];

        return view('staff.dashboard', compact('shop_id', 'dashboardData'));
    }
}