<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreShopSpecialClosedDayRequest;
use App\Models\Shop;
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
}
