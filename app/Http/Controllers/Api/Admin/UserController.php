<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private const USERS_PER_PAGE = 20;

    public function index(Request $request)
    {
        $query = User::with('owner')->latest();

        $publicId = $request->input('public_id');
        if ($publicId) {
            $query->where('public_id', 'like', "%{$publicId}%");
        }

        $paginator = $query->paginate(self::USERS_PER_PAGE)->withQueryString();

        $users = $paginator->getCollection()->map(function ($user) {
            $user->is_owner = $user->owner()->exists();
            return $user;
        });

        $paginator->setCollection($users);

        return response()->json($paginator);
    }
}
