<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopStaffController extends Controller
{
    public function index(Request $request, Shop $shop): JsonResponse
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
            // Validate sort column to prevent SQL injection
            $allowedSorts = ['id', 'created_at'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                 $query->orderBy('created_at', 'desc');
            }
        }

        $staffs = $query->paginate($request->input('per_page', 20));

        return response()->json($staffs);
    }
}
