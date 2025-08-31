<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // ダミーのダッシュボードデータ
        $dashboardData = [
            'total_shops' => 2,
            'active_contracts' => 1,
            'pending_bookings_across_shops' => 5,
            'recent_activities' => [
                ['type' => 'booking', 'description' => '店舗Aで新規予約', 'time' => '1時間前'],
                ['type' => 'contract', 'description' => '店舗Bの契約更新間近', 'time' => '昨日'],
            ],
            'shop_summaries' => [
                ['id' => 1, 'name' => '店舗A', 'today_bookings' => 3, 'status' => 'active'],
                ['id' => 2, 'name' => '店舗B', 'today_bookings' => 0, 'status' => 'inactive'],
            ],
        ];
        return view('owner.dashboard', compact('dashboardData'));
    }
}