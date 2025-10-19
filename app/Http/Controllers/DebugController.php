<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    /**
     * Log in as a specific user for debugging purposes.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginAs(User $user)
    {
        Log::info('loginAs method called for user ID: '.$user->id);
        Auth::login($user);

        // $roles = implode(', ', $roleNames);

        return redirect('/')->with('status', 'Logged in as User ID: '.$user->id);
    }
}
