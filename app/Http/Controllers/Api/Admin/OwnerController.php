<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class OwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Owner::query();

        // Eager load relationships
        $query->with('user')->withCount('contracts');

        // Filtering
        if ($request->filled('public_id')) {
            $query->whereHas('user', function (Builder $q) use ($request) {
                $q->where('public_id', 'like', '%' . $request->input('public_id') . '%');
            });
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->filled('created_at_after')) {
            $query->whereDate('created_at', '>=', $request->input('created_at_after'));
        }
        if ($request->filled('created_at_before')) {
            $query->whereDate('created_at', '<=', $request->input('created_at_before'));
        }
        if ($request->filled('contracts_count_min')) {
            $query->has('contracts', '>=', $request->input('contracts_count_min'));
        }
        if ($request->filled('contracts_count_max')) {
            $query->has('contracts', '<=', $request->input('contracts_count_max'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'public_id') {
             $query->orderBy(
                User::select('public_id')
                    ->whereColumn('users.id', 'owners.user_id')
                    ->limit(1),
                $sortOrder
            );
        } else {
            // This works for 'name', 'created_at', and 'contracts_count' (from withCount)
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        $owners = $query->paginate($perPage);

        return response()->json($owners);
    }
}
