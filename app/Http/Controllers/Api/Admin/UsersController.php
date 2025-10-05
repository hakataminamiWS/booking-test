<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        // withCount('owner') を使ってN+1問題を解決し、役割を判定
        $query = User::withCount('owner');

        // --- Dynamic Filtering ---
        $filterable = [
            'public_id' => ['users.public_id', 'like'],
            'role' => ['', 'role'], // Special handling
        ];

        foreach ($filterable as $key => $options) {
            if ($request->filled($key)) {
                [$column, $operator] = $options;
                $value = $request->input($key);

                if ($operator === 'like') {
                    $query->where($column, 'like', '%' . $value . '%');
                } elseif ($operator === 'role') {
                    if ($value === 'owner') {
                        $query->has('owner');
                    } elseif ($value === 'user') {
                        $query->doesntHave('owner');
                    }
                } else {
                    $query->where($column, $operator, $value);
                }
            }
        }

        if ($request->filled('created_at_after')) {
            $query->where('users.created_at', '>=', $request->input('created_at_after'));
        }
        if ($request->filled('created_at_before')) {
            $query->where('users.created_at', '<=', $request->input('created_at_before'));
        }

        // --- Sorting ---
        if ($request->filled('sort_by')) {
            $sortBy = $request->input('sort_by');
            $orderInput = strtolower($request->input('sort_order', 'asc'));
            $order = in_array($orderInput, ['asc', 'desc']) ? $orderInput : 'asc';

            $sortableColumns = [
                'public_id' => 'public_id',
                'created_at' => 'created_at',
            ];

            if (array_key_exists($sortBy, $sortableColumns)) {
                $query->orderBy($sortableColumns[$sortBy], $order);
            }
        } else {
            $query->latest('users.created_at'); // Default sort
        }

        $paginator = $query->paginate($request->input('per_page', 20));

        return response()->json($paginator);
    }
}