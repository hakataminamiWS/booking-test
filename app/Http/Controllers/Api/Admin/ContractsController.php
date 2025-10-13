<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\IndexContractsRequest;
use App\Models\Contract;

class ContractsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexContractsRequest $request)
    {
        $validated = $request->validated();
        $query = Contract::with('user');

        // Filtering
        if (isset($validated['name'])) {
            $query->where('name', 'like', '%' . $validated['name'] . '%');
        }

        if (isset($validated['public_id'])) {
            $query->whereHas('user', function ($q) use ($validated) {
                $q->where('public_id', $validated['public_id']);
            });
        }

        if (isset($validated['statuses'])) {
            $query->whereIn('status', $validated['statuses']);
        }

        if (isset($validated['start_date_after'])) {
            $query->where('start_date', '>=', $validated['start_date_after']);
        }

        if (isset($validated['start_date_before'])) {
            $query->where('start_date', '<=', $validated['start_date_before']);
        }

        if (isset($validated['end_date_after'])) {
            $query->where('end_date', '>=', $validated['end_date_after']);
        }

        if (isset($validated['end_date_before'])) {
            $query->where('end_date', '<=', $validated['end_date_before']);
        }

        // Sorting
        $sortBy = $validated['sort_by'] ?? 'id';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $validated['per_page'] ?? 20;
        $contracts = $query->paginate($perPage);

        return response()->json($contracts);
    }
}
