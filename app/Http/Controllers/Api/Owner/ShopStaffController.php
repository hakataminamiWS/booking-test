<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\IndexShopStaffsRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

class ShopStaffController extends Controller
{
    public function index(IndexShopStaffsRequest $request, Shop $shop): JsonResponse
    {
        $query = $shop->staffs()->with('profile');

        // Filtering
        if ($request->filled('nickname')) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where('nickname', 'like', '%'.$request->nickname.'%');
            });
        }

        if ($request->filled('type')) {
            if ($request->type === 'user') {
                $query->whereNotNull('user_id');
            } elseif ($request->type === 'frame') {
                $query->whereNull('user_id');
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'nickname') {
            $query->select('shop_staffs.*')->leftJoin('shop_staff_profiles', 'shop_staffs.id', '=', 'shop_staff_profiles.shop_staff_id')
                ->orderBy('shop_staff_profiles.nickname', $sortOrder);
        } elseif ($sortBy === 'type') {
            $query->orderBy('user_id', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $staffs = $query->paginate($request->input('per_page', 20));

        return response()->json($staffs);
    }
}
