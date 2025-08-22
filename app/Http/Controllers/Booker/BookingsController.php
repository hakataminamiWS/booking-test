<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingsController extends Controller
{
    /**
     * 予約フォームのビューを表示します。
     */
    public function create()
    {
        return view('booker.bookings.create');
    }

    /**
     * プレビュー処理
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'staff_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        // バリデーション済みのデータをセッションに保存
        $request->session()->put('booking_data', $validated);

        return redirect()->route('booker.bookings.confirm');
    }

    /**
     * 確認画面表示
     */
    public function confirm(Request $request)
    {
        // セッションからデータを取得（なければ空の配列）
        $bookingData = $request->session()->get('booking_data', []);

        // セッションにデータがなければ、作成画面に戻す
        if (empty($bookingData)) {
            return redirect()->route('booker.bookings.create');
        }

        // --- ダミーデータ ---
        // 本来は Staff::find($bookingData['staff_id']) のようにDBから取得する
        $staffList = [
            1 => ['name' => '山田 太郎'],
            2 => ['name' => '鈴木 花子'],
        ];
        $serviceList = [
            1 => ['name' => 'カット'],
            2 => ['name' => 'カラー'],
            3 => ['name' => 'パーマ'],
        ];

        $staff = $staffList[$bookingData['staff_id']] ?? null;
        $service = $serviceList[$bookingData['service_id']] ?? null;

        // ビューに渡すデータを構築
        $displayData = [
            'date' => $bookingData['date'],
            'time' => $bookingData['time'],
            'staff_name' => $staff ? $staff['name'] : '不明な担当者',
            'service_name' => $service ? $service['name'] : '不明なサービス',
        ];
        // --- ここまで ---


        return view('booker.bookings.confirm', [
            'bookingData' => $displayData
        ]);
    }


    /**
     * 予約をDBに保存します。
     */
    public function store(Request $request)
    {
        // セッションから検証済みのデータを取得
        $bookingData = $request->session()->get('booking_data');

        // セッションにデータがなければ、不正なアクセスとして作成画面に戻す
        if (empty($bookingData)) {
            return redirect()->route('booker.bookings.create')->with('error', '予約セッションの有効期限が切れました。もう一度お試しください。');
        }

        // ここで実際にデータベースに予約を保存する処理を記述します
        Log::info('予約が作成されました:', $bookingData);

        // 使用済みのセッションデータを削除
        $request->session()->forget('booking_data');

        // 完了画面にリダイレクトします
        return redirect()->route('booker.bookings.complete')
            ->with('status', 'ご予約が完了しました。');
    }

    /**
     * 予約完了ページを表示します。
     */
    public function complete()
    {
        return view('booker.bookings.complete');
    }

    /**
     * VueコンポーネントからのAjaxリクエストに応答して、
     * 特定の日付の空き状況を返します (APIエンドポイント)。
     */
    public function getAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => '日付の形式が無効です。'], 422);
        }

        $date = $request->input('date');

        // ここで実際にDBをチェックして空き状況を確認するロジックを実装します
        // この例では、単純なダミーロジックを使用します
        $is_available = !in_array(date('w', strtotime($date)), [0, 1]); // 日曜日と月曜日を定休日とする

        if ($is_available) {
            return response()->json([
                'message' => "{$date}はご予約可能です。"
            ]);
        } else {
            return response()->json([
                'message' => "申し訳ありません。{$date}は定休日です。"
            ]);
        }
    }
}