<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contract::with('user');

        // Filtering
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('public_id')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('public_id', $request->input('public_id'));
            });
        }

        if ($request->filled('statuses')) {
            $query->whereIn('status', $request->input('statuses'));
        }

        if ($request->filled('start_date_after')) {
            $query->where('start_date', '>=', $request->input('start_date_after'));
        }

        if ($request->filled('start_date_before')) {
            $query->where('start_date', '<=', $request->input('start_date_before'));
        }

        if ($request->filled('end_date_after')) {
            $query->where('end_date', '>=', $request->input('end_date_after'));
        }

        if ($request->filled('end_date_before')) {
            $query->where('end_date', '<=', $request->input('end_date_before'));
        }

        // Sorting
        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($request->input('sort_by'), $sortOrder);
        } else {
            $query->orderBy('id', 'asc');
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        $contracts = $query->paginate($perPage);

        return response()->json($contracts);
    }
}
