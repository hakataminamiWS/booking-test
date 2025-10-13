<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\IndexShopsRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShopsController extends Controller
{
    public function index(IndexShopsRequest $request): JsonResponse
    {
        $query = Auth::user()->owner->shops();

        // Filtering
        if ($request->filled('slug')) {
            $query->where('slug', 'like', '%' . $request->slug . '%');
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $shops = $query->paginate($request->input('per_page', 20));

        return response()->json($shops);
    }

    public function validateSlug(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'slug' => ['required', 'string', 'regex:/^[a-z0-9-]+$/'],
        ]);

        if ($validator->fails()) {
            return response()->json(['is_valid' => false, 'message' => $validator->errors()->first('slug')], 422);
        }

        $slug = $request->query('slug');
        $exists = Shop::where('slug', $slug)->exists();

        if ($exists) {
            return response()->json([
                'is_valid' => false,
                'message' => 'このIDは既に使用されています。'
            ]);
        }

        return response()->json(['is_valid' => true]);
    }
}
