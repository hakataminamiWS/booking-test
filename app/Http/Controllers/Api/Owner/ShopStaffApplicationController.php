<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\IndexShopStaffApplicationsRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

class ShopStaffApplicationController extends Controller
{
    public function index(IndexShopStaffApplicationsRequest $request, Shop $shop): JsonResponse
    {
        $query = $shop->staffApplications();

        // Filtering
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $applications = $query->paginate($request->input('per_page', 20));

        return response()->json($applications);
    }
}
