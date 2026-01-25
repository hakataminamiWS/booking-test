<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\IndexShopBookersRequest;
use App\Models\Shop;
use App\Models\ShopBooker;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShopBookerController extends Controller
{
    use AuthorizesRequests;

    public function index(IndexShopBookersRequest $request, Shop $shop): JsonResponse
    {
        $this->authorize('viewAny', [ShopBooker::class, $shop]);

        $query = $shop->bookers()->with('crm');

        // Filtering
        if ($request->filled('number')) {
            $query->where('shop_bookers.number', $request->number);
        }
        if ($request->filled('name')) {
            $query->where('shop_bookers.name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('contact_email')) {
            $query->where('shop_bookers.contact_email', 'like', '%' . $request->contact_email . '%');
        }
        if ($request->filled('contact_phone')) {
            $query->where('shop_bookers.contact_phone', 'like', '%' . $request->contact_phone . '%');
        }

        // Filtering for CRM fields
        if ($request->filled('name_kana')) {
            $query->whereHas('crm', function ($q) use ($request) {
                $q->where('name_kana', 'like', '%' . $request->name_kana . '%');
            });
        }
        if ($request->filled('last_booking_at_from') || $request->filled('last_booking_at_to')) {
            $query->whereHas('crm', function ($q) use ($request) {
                if ($request->filled('last_booking_at_from')) {
                    $q->whereDate('last_booking_at', '>=', $request->last_booking_at_from);
                }
                if ($request->filled('last_booking_at_to')) {
                    $q->whereDate('last_booking_at', '<=', $request->last_booking_at_to);
                }
            });
        }
        if ($request->filled('booking_count_from') || $request->filled('booking_count_to')) {
            $query->whereHas('crm', function ($q) use ($request) {
                if ($request->filled('booking_count_from')) {
                    $q->where('booking_count', '>=', $request->booking_count_from);
                }
                if ($request->filled('booking_count_to')) {
                    $q->where('booking_count', '<=', $request->booking_count_to);
                }
            });
        }

        if ($request->filled('is_guest')) {
            if (filter_var($request->is_guest, FILTER_VALIDATE_BOOLEAN)) {
                $query->whereNull('shop_bookers.user_id');
            } else {
                $query->whereNotNull('shop_bookers.user_id');
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');

        if (in_array($sortBy, ['name_kana', 'last_booking_at', 'booking_count'])) {
            $query->leftJoin('shop_bookers_crm', 'shop_bookers.id', '=', 'shop_bookers_crm.shop_booker_id')
                  ->orderBy('shop_bookers_crm.' . $sortBy, $sortOrder)
                  ->select('shop_bookers.*'); // Prevent ambiguity
        } else {
            $query->orderBy('shop_bookers.' . $sortBy, $sortOrder);
        }

        $bookers = $query->paginate($request->input('per_page', 20));

        $bookers->through(function ($booker) {
            $booker->is_guest = is_null($booker->user_id);
            return $booker;
        });

        return response()->json($bookers);
    }

    /**
     * Get the history of a specific booker.
     */
    public function history(Shop $shop, ShopBooker $booker): JsonResponse
    {
        $this->authorize('view', [$booker, $shop]);

        // Get CRM data
        $crm = $booker->crm;
        $timezone = $shop->timezone;

        // Get recent 3 bookings
        $recentBookings = $booker->bookings()
            ->orderBy('start_at', 'desc')
            ->limit(3)
            ->get(['id', 'start_at', 'menu_name', 'assigned_staff_name', 'status']);

        return response()->json([
            'booking_count' => $crm?->booking_count ?? 0,
            'last_booking_at' => $crm?->last_booking_at?->setTimezone($timezone)->format('Y-m-d H:i'),
            'note_from_booker' => $booker->note_from_booker,
            'shop_memo' => $crm?->shop_memo,
            'recent_bookings' => $recentBookings->map(function ($booking) use ($timezone) {
                return [
                    'id' => $booking->id,
                    'start_at' => $booking->start_at->setTimezone($timezone)->format('Y-m-d H:i'),
                    'menu_name' => $booking->menu_name,
                    'staff_name' => $booking->assigned_staff_name,
                    'status' => $booking->status,
                ];
            }),
        ]);
    }
}
