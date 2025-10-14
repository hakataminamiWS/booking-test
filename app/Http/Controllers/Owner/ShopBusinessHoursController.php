<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdateShopBusinessHoursRequest;
use App\Models\Shop;
use App\Models\ShopBusinessHoursRegular;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShopBusinessHoursController extends Controller
{
    use AuthorizesRequests;

    public function index(Shop $shop)
    {
        $this->authorize('view', $shop);

        $businessHours = [];
        for ($i = 0; $i < 7; $i++) {
            $businessHours[$i] = ShopBusinessHoursRegular::firstOrNew(
                ['shop_id' => $shop->id, 'day_of_week' => $i],
                ['is_open' => false, 'start_time' => null, 'end_time' => null]
            );
        }

        $specialOpenDays = $shop->shopSpecialOpenDays()
            ->whereDate('date', '>=', Carbon::today())
            ->orderBy('date', 'asc')
            ->get();

        $specialClosedDays = $shop->shopSpecialClosedDays()
            ->whereDate('end_at', '>=', Carbon::today())
            ->orderBy('start_at', 'asc')
            ->get();

        return view('owner.shops.business-hours.index', compact(
            'shop',
            'businessHours',
            'specialOpenDays',
            'specialClosedDays'
        ));
    }

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

        return redirect()->route('owner.shops.business-hours.index', $shop->slug)
            ->with('success', '営業時間を更新しました。');
    }
}
