<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('owner');

        // Filtering
        if ($request->filled('public_id')) {
            $query->where('public_id', 'like', '%' . $request->input('public_id') . '%');
        }

        // Sorting
        if ($request->filled('sort_by')) {
            $order = $request->input('sort_order', 'asc');
            $query->orderBy($request->input('sort_by'), $order);
        } else {
            $query->latest(); // Default sort
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        $paginator = $query->paginate($perPage)->withQueryString();

        // Add is_owner attribute
        $users = $paginator->getCollection()->map(function ($user) {
            $user->is_owner = $user->owner()->exists();
            return $user;
        });

        $paginator->setCollection($users);

        return response()->json($paginator);
    }
}
