<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Owner;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    // ... (existing code) ...

    public function index()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        return view('admin.users.show', [
            'user' => $user,
            'is_owner' => $user->owner()->exists(),
            'has_contract' => $user->contract()->exists(),
        ]);
    }

    public function addRole(Request $request, User $user)
    {
        // すでにオーナーの場合は何もしない
        if ($user->owner) {
            return redirect()->route('admin.users.show', $user)->with('error', 'このユーザーは既にオーナーです。');
        }

        Owner::create([
            'user_id' => $user->id,
            'name' => $user->public_id,
        ]);

        return redirect()->route('admin.users.show', $user)->with('success', 'ユーザーをオーナーに設定しました。');
    }

    public function removeRole(Request $request, User $user)
    {
        if ($user->owner) {
            $user->owner->delete();
            return redirect()->route('admin.users.show', $user)->with('success', 'オーナーの役割を解除しました。');
        }

        return redirect()->route('admin.users.show', $user)->with('error', 'このユーザーはオーナーではありません。');
    }

    // ... (existing code) ...
}
