<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreShopSpecialClosedDayRequest;
use App\Http\Requests\Owner\UpdateShopSpecialClosedDayRequest;
use App\Models\Shop;
use App\Models\ShopSpecialClosedDay;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShopSpecialClosedDaysController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the form for creating a new resource.
     */
    public function create(Shop $shop)
    {
        $this->authorize('update', $shop);

        return view('owner.shops.business-hours.special-closed-days.create', compact('shop'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopSpecialClosedDayRequest $request, Shop $shop)
    {
        $this->authorize('update', $shop);

        $validated = $request->validated();

        $shop->shopSpecialClosedDays()->create($validated);

        return redirect()->route('owner.shops.business-hours.index', $shop->slug)
                         ->with('success', '特別休業日を登録しました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop, ShopSpecialClosedDay $special_closed_day)
    {
        $this->authorize('update', $shop);

        return view('owner.shops.business-hours.special-closed-days.edit', compact('shop', 'special_closed_day'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopSpecialClosedDayRequest $request, Shop $shop, ShopSpecialClosedDay $special_closed_day)
    {
        $this->authorize('update', $shop);

        $validated = $request->validated();

        $special_closed_day->update($validated);

        return redirect()->route('owner.shops.business-hours.index', $shop->slug)
                         ->with('success', '特別休業日を更新しました。');
    }
}
