<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingsController extends Controller
{
    /**
     * 予約一覧
     */
    public function index($shop_id)
    {
        $bookings = [
            (object)['id' => 1, 'booking_date' => '2025-09-10', 'booking_time' => '10:00', 'status' => 'confirmed'],
            (object)['id' => 2, 'booking_date' => '2025-09-15', 'booking_time' => '14:30', 'status' => 'confirmed'],
        ];
        return view('booker.bookings.index', compact('shop_id', 'bookings'));
    }

    /**
     * 予約フォームのビューを表示します。
     */
    public function create($shop_id)
    {
        return view('booker.bookings.create', ['shop_id' => $shop_id]);
    }

    /**
     * プレビュー処理
     */
    public function preview(Request $request, $shop_id)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'staff_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        // バリデーション済みのデータをセッションに保存
        $request->session()->put('booking_data', $validated);

        return redirect()->route('booker.bookings.confirm', ['shop_id' => $shop_id]);
    }

    /**
     * 確認画面表示
     */
    public function confirm(Request $request, $shop_id)
    {
        // セッションからデータを取得（なければ空の配列）
        $bookingData = $request->session()->get('booking_data', []);

        // セッションにデータがなければ、作成画面に戻す
        if (empty($bookingData)) {
            return redirect()->route('booker.bookings.create', ['shop_id' => $shop_id]);
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
            'shop_id' => $shop_id,
            'bookingData' => $displayData
        ]);
    }


    /**
     * 予約をDBに保存します。
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
        ]);

        // 将来ここでDBへの保存と重複チェックを行う
        Log::info("予約が作成されました: shop_id={$shop_id}", $validated);

        // 完了ページにリダイレクトする際に、予約内容をセッションに一時保存する
        return redirect()->route('booker.bookings.complete', ['shop_id' => $shop_id])
            ->with('bookingDetails', $validated);
    }

    /**
     * 予約詳細
     */
    public function show($shop_id, $booking_id)
    {
        // ダミーの予約詳細データ
        $bookingDetails = [
            'id' => $booking_id,
            'date' => '2025-09-10',
            'time' => '10:00',
            'staff_name' => '山田 太郎',
            'service_name' => 'カット (60分)',
            'status' => 'confirmed',
            'notes' => '特にありません。'
        ];

        return view('booker.bookings.show', compact('shop_id', 'booking_id', 'bookingDetails'));
    }

    /**
     * 予約変更
     */
    public function edit($shop_id, $booking_id)
    {
        // TODO: Implement edit method.
        return response("Edit booking for shop {$shop_id}, booking {$booking_id}");
    }

    /**
     * 予約更新
     */
    public function update(Request $request, $shop_id, $booking_id)
    {
        // TODO: Implement update method.
        return response("Update booking for shop {$shop_id}, booking {$booking_id}");
    }

    /**
     * 予約完了ページを表示します。
     */
    public function complete(Request $request, $shop_id)
    {
        // セッションからフラッシュデータを取得
        $bookingDetails = $request->session()->get('bookingDetails');

        // セッションに予約情報がなければ、不正なアクセスとして作成画面に戻す
        if (!$bookingDetails) {
            return redirect()->route('booker.bookings.create', ['shop_id' => $shop_id]);
        }

        return view('booker.bookings.complete', [
            'shop_id' => $shop_id,
            'bookingDetails' => $bookingDetails
        ]);
    }

    /**
     * VueコンポーネントからのAjaxリクエストに応答して、
     * 特定の日付の空き状況を返します (APIエンドポイント)。
     */
    public function getAvailability(Request $request, $shop_id)
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
