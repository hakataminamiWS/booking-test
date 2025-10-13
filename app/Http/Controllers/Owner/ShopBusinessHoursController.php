<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdateShopBusinessHoursRequest;
use App\Models\Shop;
use App\Models\ShopBusinessHoursRegular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShopBusinessHoursController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop)
    {
        $this->authorize('update', $shop);

        $businessHours = [];
        for ($i = 0; $i < 7; $i++) {
            $businessHours[$i] = ShopBusinessHoursRegular::firstOrNew(
                ['shop_id' => $shop->id, 'day_of_week' => $i],
                ['is_open' => false, 'start_time' => null, 'end_time' => null]
            );
        }

        return view('owner.shops.business-hours.regular.edit', compact('shop', 'businessHours'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopBusinessHoursRequest $request, Shop $shop)
    {
        $this->authorize('update', $shop);

        $validated = $request->validated();

        foreach ($validated['business_hours'] as $data) {
            ShopBusinessHoursRegular::updateOrCreate(
                ['shop_id' => $shop->id, 'day_of_week' => $data['day_of_week']],
                [
                    'is_open' => $data['is_open'],
                    'start_time' => $data['is_open'] ? $data['start_time'] : null,
                    'end_time' => $data['is_open'] ? $data['end_time'] : null,
                ]
            );
        }

        return redirect()->route('owner.shops.show', $shop->slug)->with('success', '営業時間を更新しました。');
    }
}
