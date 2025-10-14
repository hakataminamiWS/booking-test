<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreShopSpecialOpenDayRequest;
use App\Models\Shop;
use App\Models\ShopSpecialOpenDay;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShopSpecialOpenDaysController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the form for creating a new resource.
     */
    public function create(Shop $shop)
    {
        $this->authorize('update', $shop);

        return view('owner.shops.business-hours.special-open-days.create', compact('shop'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopSpecialOpenDayRequest $request, Shop $shop)
    {
        $this->authorize('update', $shop);

        $validated = $request->validated();

        $shop->shopSpecialOpenDays()->create($validated);

        return redirect()->route('owner.shops.business-hours.index', $shop->slug)
                         ->with('success', '特別営業日を登録しました。');
    }
}
