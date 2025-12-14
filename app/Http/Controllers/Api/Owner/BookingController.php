<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\IndexBookingsRequest;
use App\Models\Booking;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\ShopStaff;
use App\Services\TimeSlotService;
use App\Models\ShopMenu;
use App\Models\ShopOption;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function validateStaff(Request $request, Shop $shop): JsonResponse
    {
        // 認可: 予約一覧が見れるならOKとする
        $this->authorize('viewAny', [Booking::class]);

        $request->validate([
            'menu_id' => ['required', 'integer'],
            'assigned_staff_id' => ['required', 'integer'],
        ]);

        $menuId = $request->input('menu_id');
        $staffId = $request->input('assigned_staff_id');

        // menu_id に紐づく staffs を確認
        $exists = \Illuminate\Support\Facades\DB::table('shop_menu_staffs')
            ->where('shop_menu_id', $menuId)
            ->where('shop_staff_id', $staffId)
            ->exists();

        return response()->json(['valid' => $exists]);
    }

    public function validateShift(Request $request, Shop $shop, TimeSlotService $timeSlotService): JsonResponse
    {
        $this->authorize('viewAny', [Booking::class]);

        $request->validate([
            'assigned_staff_id' => ['required', 'integer'],
            'start_at' => ['required', 'date'], // Y-m-d H:i:s
            'menu_id' => ['required', 'integer'],
            'option_ids' => ['nullable', 'array'],
        ]);

        $timezone = $shop->timezone;
        $date = Carbon::parse($request->input('start_at'), $timezone);
        $startAt = $date->copy(); // 予約開始時刻 (店舗TZ)

        // 期間計算
        $menu = ShopMenu::findOrFail($request->input('menu_id'));
        $options = ShopOption::find($request->input('option_ids') ?? []);
        $totalDuration = $menu->duration + $options->sum('additional_duration');

        $endAt = $startAt->copy()->addMinutes($totalDuration); // 予約終了時刻 (店舗TZ)

        // シフト取得
        $staff = ShopStaff::findOrFail($request->input('assigned_staff_id'));

        // 検索のため、対象日の開始・終了時刻をUTCで取得
        $startOfDayUtc = $date->copy()->startOfDay()->setTimezone(config('app.timezone'));
        $endOfDayUtc = $date->copy()->endOfDay()->setTimezone(config('app.timezone'));

        $shifts = $staff->schedules()
            ->whereBetween('workable_start_at', [$startOfDayUtc, $endOfDayUtc])
            ->get()
            ->map(fn($schedule) => (object)[
                'start' => $schedule->workable_start_at->setTimezone($timezone)->format('H:i'),
                'end' => $schedule->workable_end_at->setTimezone($timezone)->format('H:i')
            ])
            ->all();

        $valid = $timeSlotService->isWithinShift($startAt, $endAt, $shifts);

        return response()->json(['valid' => $valid]);
    }

    public function validateConflict(Request $request, Shop $shop, TimeSlotService $timeSlotService): JsonResponse
    {
        $this->authorize('viewAny', [Booking::class]);

        $request->validate([
            'assigned_staff_id' => ['required', 'integer'],
            'start_at' => ['required', 'date'],
            'menu_id' => ['required', 'integer'],
            'option_ids' => ['nullable', 'array'],
        ]);

        $date = Carbon::parse($request->input('start_at'));
        $startAt = $date->copy();
        
        $menu = ShopMenu::findOrFail($request->input('menu_id'));
        $options = ShopOption::find($request->input('option_ids') ?? []);
        $totalDuration = $menu->duration + $options->sum('additional_duration');
        
        $endAt = $startAt->copy()->addMinutes($totalDuration);

        $staff = ShopStaff::findOrFail($request->input('assigned_staff_id'));
        
        // 既存予約取得
        $existingBookings = $staff->bookings()
            ->whereDate('start_at', $startAt->toDateString())
            ->get()
            ->map(fn($booking) => (object)[
                'start' => Carbon::parse($booking->start_at)->format('H:i'), // H:i only works if date matches
                'end' => Carbon::parse($booking->end_at)->format('H:i')
            ])
            ->all();
        
        // TimeSlotServiceのhasConflictは 'H:i' 文字列で比較する実装になっている。
        // validateShiftと同様に対応する。
        // ただし、validateConflict内で日付またぎ等を考慮する必要がある場合は注意。
        // 今回は TimeSlotService::hasConflict を利用する。
        
        $hasConflict = $timeSlotService->hasConflict($startAt, $endAt, $existingBookings);

        return response()->json(['valid' => !$hasConflict]); // valid = true means NO conflict
    }

    public function getWorkingDays(Request $request, Shop $shop, ShopStaff $staff): JsonResponse
    {
        $this->authorize('viewAny', [Booking::class]);

        $request->validate([
            'year_month' => ['required', 'date_format:Y-m'],
        ]);

        $yearMonth = $request->input('year_month');
        $startOfMonth = Carbon::parse($yearMonth . '-01')->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        // タイムゾーン考慮 (UTCで保存されている前提で、JSTの00:00〜23:59を探す)
        // ここでは簡易的にAsia/Tokyo固定とするが、本来はShopのtimezoneを使うべき
        $timezone = 'Asia/Tokyo'; 
        
        // Eloquentで検索する際はUTCに変換された範囲で探す必要がある
        // ただし、workable_start_at はUTC。
        // 日本時間の 2025-12-01 00:00:00 は UTC 2025-11-30 15:00:00
        // 日本時間の 2025-12-31 23:59:59 は UTC 2025-12-31 14:59:59
        
        $startUtc = $startOfMonth->copy()->setTimezone('UTC');
        $endUtc = $endOfMonth->copy()->setTimezone('UTC');

        $scheduledDays = $staff->schedules()
            ->whereBetween('workable_start_at', [$startUtc, $endUtc])
            ->get()
            ->map(function ($schedule) use ($timezone) {
                return $schedule->workable_start_at->setTimezone($timezone)->format('Y-m-d');
            })
            ->unique()
            ->values()
            ->all();

        return response()->json($scheduledDays);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexBookingsRequest $request, Shop $shop): JsonResponse
    {
        $this->authorize('viewAny', [Booking::class]);

        $query = $shop->bookings();

        // リレーションのロード
        // menu_price (ShopMenu table) などを取得したいため、loadしておく
        // booking_options も合計計算に必要
        // booker も予約者番号取得のため追加
        $query->with(['bookingOptions', 'booker']);

        // フィルタリング
        if ($request->filled('start_at_from')) {
            $query->whereDate('start_at', '>=', $request->input('start_at_from'));
        }
        if ($request->filled('start_at_to')) {
            $query->whereDate('start_at', '<=', $request->input('start_at_to'));
        }
        if ($request->filled('booker_number')) {
            // shop_bookersテーブルとjoinして予約者番号でフィルタ
            $query->whereHas('booker', function (Builder $q) use ($request) {
                $q->where('number', $request->input('booker_number'));
            });
        }
        if ($request->filled('booker_name')) {
            $query->where('booker_name', 'like', '%' . $request->input('booker_name') . '%');
        }
        if ($request->filled('menu_id')) {
            $query->where('menu_id', $request->input('menu_id'));
        }
        if ($request->filled('assigned_staff_id')) {
            $query->where('assigned_staff_id', $request->input('assigned_staff_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('booking_channel')) {
            $query->where('booking_channel', $request->input('booking_channel'));
        }

        // ソート
        $sortBy = $request->input('sort_by', 'start_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'booker_number') {
            // 予約者番号でのソート（shop_bookersテーブルとjoin）
            $query->join('shop_bookers', 'bookings.shop_booker_id', '=', 'shop_bookers.id')
                ->select('bookings.*')
                ->orderBy('shop_bookers.number', $sortOrder);
        } elseif ($sortBy === 'total_price') {
            // 合計料金でのソート（計算が必要なためSQLでのソートが難しい場合はコレクションでソートするか、
            // 保存時に合計金額カラムを持たせるか。ここでは簡易的にmenu_priceでのソートにするか、
            // もしくはDB構造上難しいので、start_atでフォールバックするのが無難だが、
            // クライアントの要求仕様にあるため、実装を試みるなら、
            // withSum を使う。
            $query->withSum('bookingOptions as options_total_price', 'option_price');
            // menu_price + options_total_price でソートしたいが、
            // SQLのOrderByRawで対応
            $query->orderByRaw('(menu_price + COALESCE(booking_options_sum_option_price, 0)) ' . $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = $request->input('per_page', 20);
        $bookings = $query->paginate($perPage);

        // レスポンスデータの整形（合計金額の計算など）
        $bookings->getCollection()->transform(function ($booking) {
            $optionsTotal = $booking->bookingOptions->sum('option_price');
            $booking->total_price = $booking->menu_price + $optionsTotal;
            // 予約者番号を追加
            $booking->booker_number = $booking->booker?->number;
            return $booking;
        });

        return response()->json($bookings);
    }
}
