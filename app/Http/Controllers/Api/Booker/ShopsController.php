<?php

namespace App\Http\Controllers\Api\Booker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Booker\IndexShopsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ShopsController extends Controller
{
    /**
     * Get a paginated list of shops the authenticated user is registered as a booker.
     */
    public function index(IndexShopsRequest $request): JsonResponse
    {
        $user = Auth::user();

        $query = $user->shopBookers()
            ->with(['shop', 'crm'])
            ->whereHas('shop');

        // Filtering by shop name
        if ($request->filled('name')) {
            $query->whereHas('shop', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        // Filtering by last booking date
        if ($request->filled('last_booking_at_from')) {
            $query->whereHas('crm', function ($q) use ($request) {
                $q->whereDate('last_booking_at', '>=', $request->last_booking_at_from);
            });
        }
        if ($request->filled('last_booking_at_to')) {
            $query->whereHas('crm', function ($q) use ($request) {
                $q->whereDate('last_booking_at', '<=', $request->last_booking_at_to);
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');

        if ($sortBy === 'name') {
            $query->join('shops', 'shop_bookers.shop_id', '=', 'shops.id')
                ->orderBy('shops.name', $sortOrder)
                ->select('shop_bookers.*');
        } elseif ($sortBy === 'last_booking_at') {
            $query->leftJoin('shop_bookers_crm', 'shop_bookers.id', '=', 'shop_bookers_crm.shop_booker_id')
                ->orderBy('shop_bookers_crm.last_booking_at', $sortOrder)
                ->select('shop_bookers.*');
        } elseif ($sortBy === 'booking_count') {
            $query->leftJoin('shop_bookers_crm', 'shop_bookers.id', '=', 'shop_bookers_crm.shop_booker_id')
                ->orderBy('shop_bookers_crm.booking_count', $sortOrder)
                ->select('shop_bookers.*');
        }

        $shopBookers = $query->paginate($request->input('per_page', 20));

        // Transform the data for the frontend
        $transformedData = $shopBookers->through(function ($shopBooker) {
            return [
                'id' => $shopBooker->id,
                'shop_id' => $shopBooker->shop_id,
                'shop_name' => $shopBooker->shop->name ?? '',
                'shop_slug' => $shopBooker->shop->slug ?? '',
                'last_booking_at' => $shopBooker->crm->last_booking_at ?? null,
                'booking_count' => $shopBooker->crm->booking_count ?? 0,
            ];
        });

        return response()->json($transformedData);
    }
}
