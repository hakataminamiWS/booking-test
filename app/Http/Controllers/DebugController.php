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

    public function loginAsUnregistered(\App\Models\Shop $shop)
    {
        // Use User ID 6 as the designated "Unregistered" test user
        $user = User::find(6);
        if (!$user) {
             // If ID 6 doesn't exist, create it (safe for local/staging debug)
             $user = User::factory()->create([
                 'id' => 6,
                 'name' => '未登録テストユーザー',
                 'email' => 'unregistered@example.com',
                 'password' => bcrypt('password'),
             ]);
        }

        // Force delete any existing profile for this shop to simulate "unregistered" state
        \App\Models\ShopBooker::where('shop_id', $shop->id)->where('user_id', $user->id)->delete();

        Auth::login($user);
        return redirect('/')->with('status', '未登録ユーザー (ID: '.$user->id.') としてログインしました。プロフィールは削除されました。');
    }
}
