<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display the dashboard for the specified shop.
     */
    public function show(\App\Models\Shop $shop)
    {
        $booker = \App\Models\ShopBooker::where('shop_id', $shop->id)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->first();

        // If not registered, redirect to profile creation
        if (!$booker) {
            return redirect()->route('booker.profile.create', ['shop' => $shop]);
        }

        $booker->load('crm');

        return view('booker.shops.show', compact('shop', 'booker'));
    }
}
