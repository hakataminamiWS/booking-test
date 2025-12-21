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
}
