<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Staff\IndexShopBookersRequest;
use App\Models\Shop;
use App\Models\ShopBooker;
use App\Models\ShopStaff;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ShopBookerController extends Controller
{
    private function getAuthenticatedStaff(Shop $shop): ShopStaff
    {
        return ShopStaff::where('shop_id', $shop->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    public function index(IndexShopBookersRequest $request, Shop $shop): JsonResponse
    {
        $this->getAuthenticatedStaff($shop);

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

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');

        if (in_array($sortBy, ['name_kana', 'last_booking_at', 'booking_count'])) {
            $query->leftJoin('shop_bookers_crm', 'shop_bookers.id', '=', 'shop_bookers_crm.shop_booker_id')
                  ->orderBy('shop_bookers_crm.' . $sortBy, $sortOrder)
                  ->select('shop_bookers.*');
        } else {
            $query->orderBy('shop_bookers.' . $sortBy, $sortOrder);
        }

        $bookers = $query->paginate($request->input('per_page', 20));

        return response()->json($bookers);
    }
}
