<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.owners.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $publicId)
    {
        $user = User::where('public_id', $publicId)->firstOrFail();
        $owner = Owner::where('user_id', $user->id)
                      ->with(['user', 'contracts', 'shops'])
                      ->firstOrFail();

        return view('admin.owners.show', compact('owner'));
    }
}
