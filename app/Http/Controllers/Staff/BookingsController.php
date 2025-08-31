<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($shop_id)
    {
        // ダミーの予約データ
        $bookings = [
            (object)['id' => 1, 'booker_name' => '田中 太郎', 'booking_date' => '2025-09-01', 'booking_time' => '10:00', 'status' => 'confirmed'],
            (object)['id' => 2, 'booker_name' => '鈴木 次郎', 'booking_date' => '2025-09-01', 'booking_time' => '11:30', 'status' => 'pending'],
            (object)['id' => 3, 'booker_name' => '佐藤 三郎', 'booking_date' => '2025-09-02', 'booking_time' => '14:00', 'status' => 'canceled'],
        ];

        return view('staff.bookings.index', compact('shop_id', 'bookings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($shop_id)
    {
        return view('staff.bookings.create', compact('shop_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $shop_id)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'staff_id' => ['required', 'integer'],
            'staff_name' => ['required', 'string'],
            'service_id' => ['required', 'integer'],
            'service_name' => ['required', 'string'],
            'time' => ['required', 'date_format:H:i'],
            'booker_name' => ['required', 'string'],
            'booker_email' => ['nullable', 'email'],
            'booker_tel' => ['nullable', 'string'],
        ]);

        Log::info("Staffが予約を作成しました: shop_id={$shop_id}", $validated);

        // 予約確認画面にリダイレクト
        return redirect()->route('staff.bookings.confirm', ['shop_id' => $shop_id])
            ->with('bookingDetails', $validated); // 予約内容をセッションに保存
    }

    /**
     * Show the booking confirmation screen.
     */
    public function confirm(Request $request, $shop_id)
    {
        $bookingDetails = $request->session()->get('bookingDetails');

        if (!$bookingDetails) {
            // セッションにデータがなければ、予約作成画面に戻す
            return redirect()->route('staff.bookings.create', ['shop_id' => $shop_id]);
        }

        return view('staff.bookings.confirm', compact('shop_id', 'bookingDetails'));
    }
}