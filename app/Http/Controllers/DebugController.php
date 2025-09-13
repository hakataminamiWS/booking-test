<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    /**
     * Log in as a specific user for debugging purposes.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginAs(User $user)
    {
        Log::info('loginAs method called for user ID: ' . $user->id);
        Auth::login($user);

        // ユーザーの役割を判定して表示
        $roleNames = [];
        if ($user->isAdmin()) {
            $roleNames[] = 'Admin';
        }
        if ($user->shops()->exists()) {
            $roleNames[] = 'Owner';
        }
        if ($user->staffShops()->exists()) {
            $roleNames[] = 'Staff';
        }
        if (empty($roleNames)) {
            $roleNames[] = 'Booker';
        }

        $roles = implode(', ', $roleNames);

        return redirect('/')->with('status', "Logged in as User ID: " . $user->id . ' (Roles: ' . $roles . ')');
    }
}