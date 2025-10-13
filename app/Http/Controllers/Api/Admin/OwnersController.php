<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\IndexOwnersRequest;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class OwnersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexOwnersRequest $request)
    {
        $validated = $request->validated();
        $query = Owner::query();

        // Eager load relationships
        $query->with('user')->withCount('contracts');

        // Filtering
        if (isset($validated['public_id'])) {
            $query->whereHas('user', function (Builder $q) use ($validated) {
                $q->where('public_id', 'like', '%' . $validated['public_id'] . '%');
            });
        }
        if (isset($validated['name'])) {
            $query->where('name', 'like', '%' . $validated['name'] . '%');
        }
        if (isset($validated['created_at_after'])) {
            $query->whereDate('created_at', '>=', $validated['created_at_after']);
        }
        if (isset($validated['created_at_before'])) {
            $query->whereDate('created_at', '<=', $validated['created_at_before']);
        }
        if (isset($validated['contracts_count_min'])) {
            $query->has('contracts', '>=', $validated['contracts_count_min']);
        }
        if (isset($validated['contracts_count_max'])) {
            $query->has('contracts', '<=', $validated['contracts_count_max']);
        }

        // Sorting
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';

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
        $perPage = $validated['per_page'] ?? 20;
        $owners = $query->paginate($perPage);

        return response()->json($owners);
    }
}
