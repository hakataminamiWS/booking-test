<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class OwnersController extends Controller
{
    public function index()
    {
        // 'owner'ロールを取得
        $ownerRole = Role::where('name', 'owner')->first();

        if (!$ownerRole) {
            return response()->json(['owners' => []]);
        }

        // user_rolesテーブルを経由して、ownerロールを持つユーザーを取得
        $owners = User::whereHas('roles', function ($query) use ($ownerRole) {
            $query->where('role_id', $ownerRole->id);
        })->get();

        return response()->json(['owners' => $owners]);
    }

    public function show($owner_id)
    {
        $ownerDetails = [
            'id' => $owner_id,
            'name' => "オーナー{$owner_id}",
            'email' => "owner{$owner_id}@example.com",
            'contract_status' => 'active',
            'shops' => [
                ['id' => 101, 'name' => '店舗X'],
                ['id' => 102, 'name' => '店舗Y'],
            ]
        ];
        return view('admin.owners.show', compact('owner_id', 'ownerDetails'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $owner = new User();
        $owner->uuid = \Illuminate\Support\Str::uuid();
        $owner->name = $validated['name'];
        $owner->email = $validated['email'];
        // Note: is_guest defaults to false, which is correct.
        $owner->save();

        // Assign owner role
        $ownerRole = Role::where('name', 'owner')->firstOrFail();
        // Note: This assumes a direct role assignment. If owner role is always tied to a shop,
        // this logic will need to be adjusted when creating the shop.
        $owner->roles()->attach($ownerRole->id);

        return response()->json(['owner' => $owner], 201);
    }
}