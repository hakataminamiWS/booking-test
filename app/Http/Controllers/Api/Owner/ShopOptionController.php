<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\IndexShopOptionsRequest;
use App\Models\Shop;
use App\Models\ShopOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShopOptionController extends Controller
{
    use AuthorizesRequests;

    public function index(IndexShopOptionsRequest $request, Shop $shop): JsonResponse
    {
        $this->authorize('viewAny', [ShopOption::class, $shop]);

        $query = $shop->options();

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
        if ($request->filled('additional_duration_from')) {
            $query->where('additional_duration', '>=', $request->additional_duration_from);
        }
        if ($request->filled('additional_duration_to')) {
            $query->where('additional_duration', '<=', $request->additional_duration_to);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $options = $query->paginate($request->input('per_page', 20));

        return response()->json($options);
    }
}
