<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopEntryController extends Controller
{
    /**
     * Display the shop entry page.
     */
    public function show(\App\Models\Shop $shop)
    {
        // If logged in and already a member, redirect to shop my page (DISABLED)
        // if (\Illuminate\Support\Facades\Auth::check()) {
        //     $booker = \App\Models\ShopBooker::where('shop_id', $shop->id)
        //         ->where('user_id', \Illuminate\Support\Facades\Auth::id())
        //         ->first();

        //     if ($booker) {
        //         return redirect()->route('booker.shop.show', ['shop' => $shop]);
        //     }
        // }

        return view('shop.entry', compact('shop'));
    }
}
