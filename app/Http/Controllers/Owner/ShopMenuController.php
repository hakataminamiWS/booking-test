<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreShopMenuRequest;
use App\Http\Requests\Owner\UpdateShopMenuRequest;
use App\Models\Shop;
use App\Models\ShopMenu;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShopMenuController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Shop $shop)
    {
        $this->authorize('view', $shop);

        return view('owner.shops.menus.index', compact('shop'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Shop $shop)
    {
        $this->authorize('update', $shop);

        $staffs = $shop->staffs()->with('profile')->get();
        $options = $shop->options;

        return view('owner.shops.menus.create', compact('shop', 'staffs', 'options'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopMenuRequest $request, Shop $shop)
    {
        $validated = $request->validated();

        $staffIds = $validated['staff_ids'] ?? [];
        unset($validated['staff_ids']);

        $optionIds = $validated['option_ids'] ?? [];
        unset($validated['option_ids']);

        $menu = $shop->menus()->create($validated);

        if (!empty($staffIds)) {
            $menu->staffs()->attach($staffIds);
        }

        if (!empty($optionIds)) {
            $menu->options()->attach($optionIds);
        }

        return redirect()->route('owner.shops.menus.index', $shop)
            ->with('success', 'メニューを登録しました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop, ShopMenu $menu)
    {
        $this->authorize('update', $menu);

        $menu->load('staffs', 'options');
        $staffs = $shop->staffs()->with('profile')->get();
        $options = $shop->options;

        return view('owner.shops.menus.edit', compact('shop', 'menu', 'staffs', 'options'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopMenuRequest $request, Shop $shop, ShopMenu $menu)
    {
        $validated = $request->validated();

        $staffIds = $validated['staff_ids'] ?? [];
        unset($validated['staff_ids']);

        $optionIds = $validated['option_ids'] ?? [];
        unset($validated['option_ids']);

        $menu->update($validated);
        $menu->staffs()->sync($staffIds);
        $menu->options()->sync($optionIds);

        return redirect()->route('owner.shops.menus.index', $shop)
            ->with('success', 'メニューを更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop, ShopMenu $menu)
    {
        $this->authorize('delete', $menu);

        $menu->delete();

        return redirect()->route('owner.shops.menus.index', $shop)
            ->with('success', 'メニューを削除しました。');
    }
}
