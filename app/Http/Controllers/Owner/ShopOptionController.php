<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreShopOptionRequest;
use App\Http\Requests\Owner\UpdateShopOptionRequest;
use App\Models\Shop;
use App\Models\ShopOption;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShopOptionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Shop $shop)
    {
        $this->authorize('view', $shop);

        return view('owner.shops.options.index', compact('shop'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Shop $shop)
    {
        $this->authorize('update', $shop);

        return view('owner.shops.options.create', compact('shop'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopOptionRequest $request, Shop $shop)
    {
        $validated = $request->validated();

        $shop->options()->create($validated);

        return redirect()->route('owner.shops.options.index', $shop)
            ->with('success', 'オプションを登録しました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop, ShopOption $option)
    {
        $this->authorize('update', $option);

        return view('owner.shops.options.edit', compact('shop', 'option'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopOptionRequest $request, Shop $shop, ShopOption $option)
    {
        $validated = $request->validated();

        $option->update($validated);

        return redirect()->route('owner.shops.options.index', $shop)
            ->with('success', 'オプションを更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop, ShopOption $option)
    {
        $this->authorize('delete', $option);

        $option->delete();

        return redirect()->route('owner.shops.options.index', $shop)
            ->with('success', 'オプションを削除しました。');
    }
}
