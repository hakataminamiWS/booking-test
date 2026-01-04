<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booker\UpdateProfileRequest;
use App\Models\Shop;
use App\Models\ShopBooker;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the booker's profile.
     */
    public function edit(Shop $shop)
    {
        $booker = $this->getAuthenticatedBooker($shop);
        $booker->load('crm');

        return view('booker.profile.edit', compact('shop', 'booker'));
    }

    /**
     * Show the form for creating a new profile.
     */
    public function create(Shop $shop)
    {
        // If already registered, redirect to booking create
        if (ShopBooker::where('shop_id', $shop->id)->where('user_id', Auth::id())->exists()) {
             return redirect()->route('booker.bookings.create', ['shop' => $shop]);
        }

        $user = Auth::user();

        return view('booker.profile.create', compact('shop', 'user'));
    }

    /**
     * Store a newly created profile.
     */
    public function store(UpdateProfileRequest $request, Shop $shop)
    {
        // Double check if already exists
        if (ShopBooker::where('shop_id', $shop->id)->where('user_id', Auth::id())->exists()) {
             return redirect()->route('booker.bookings.create', ['shop' => $shop]);
        }

        $validated = $request->validated();

        $shop->bookers()->create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'note_from_booker' => $validated['note_from_booker'] ?? null,
        ]);

        return redirect()->route('booker.bookings.create', ['shop' => $shop])
            ->with('success', 'プロフィールを登録しました。');
    }

    /**
     * Update the booker's profile.
     */
    public function update(UpdateProfileRequest $request, Shop $shop)
    {
        $booker = $this->getAuthenticatedBooker($shop);
        $validated = $request->validated();

        // Update ShopBooker
        $booker->update([
            'name' => $validated['name'],
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'note_from_booker' => $validated['note_from_booker'] ?? null,
        ]);

        return redirect()->route('booker.profile.edit', ['shop' => $shop])
            ->with('success', 'プロフィールを更新しました。');
    }

    private function getAuthenticatedBooker(Shop $shop): ShopBooker
    {
        $booker = ShopBooker::where('shop_id', $shop->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return $booker;
    }

    /**
     * Delete the booker's profile (withdrawal).
     */
    public function destroy(\App\Http\Requests\Booker\DestroyProfileRequest $request, Shop $shop)
    {
        $booker = $this->getAuthenticatedBooker($shop);

        // Nullify shop_booker_id in bookings to preserve booking history
        \App\Models\Booking::where('shop_booker_id', $booker->id)
            ->update(['shop_booker_id' => null]);

        // Delete the booker (shop_bookers_crm will be deleted via CASCADE)
        $booker->delete();

        return redirect()->route('shop.entry', ['shop' => $shop])
            ->with('success', '退会しました。');
    }
}
