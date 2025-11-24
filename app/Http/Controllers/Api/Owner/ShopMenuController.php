<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\IndexShopMenusRequest;
use App\Models\Shop;
use App\Models\ShopMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShopMenuController extends Controller
{
    use AuthorizesRequests;

    public function index(IndexShopMenusRequest $request, Shop $shop): JsonResponse
    {
        $this->authorize('viewAny', [ShopMenu::class, $shop]);

        $query = $shop->menus();

        // Filtering
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('price_from')) {
            $query->where('price', '>=', $request->price_from);
        }
        if ($request->filled('price_to')) {
            $query->where('price', '<=', $request->price_to);
        }
        if ($request->filled('duration_from')) {
            $query->where('duration', '>=', $request->duration_from);
        }
        if ($request->filled('duration_to')) {
            $query->where('duration', '<=', $request->duration_to);
        }
        if ($request->filled('requires_staff_assignment')) {
            $query->where('requires_staff_assignment', $request->boolean('requires_staff_assignment'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $menus = $query->with(['staffs.profile', 'options'])->paginate($request->input('per_page', 20));

        return response()->json($menus);
    }
}