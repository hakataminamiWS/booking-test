<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreShopRequest;
use App\Http\Requests\Owner\UpdateShopRequest;
use App\Models\Shop;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShopsController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $contract = $user->contract;

        $maxShops = $contract ? $contract->max_shops : 0;
        $currentShopsCount = $user->shops()->count();

        return view('owner.shops.index', compact('maxShops', 'currentShopsCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Shop::class);

        return view('owner.shops.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopRequest $request)
    {
        $this->authorize('create', Shop::class);

        $validated = $request->validated();
        $validated['owner_user_id'] = auth()->id();

        Shop::create($validated);

        return redirect()->route('owner.shops.index')
            ->with('success', '店舗を登録しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop) // ルートモデルバインディング
    {
        $this->authorize('view', $shop);

        return view('owner.shops.show', compact('shop'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop)
    {
        $this->authorize('update', $shop);

        return view('owner.shops.edit', compact('shop'));
    }

    /**
     * Update the specified resource in storage.
     */
        public function update(UpdateShopRequest $request, Shop $shop)
        {
            $shop->update($request->validated());
    
            return redirect()->route('owner.shops.show', $shop)
                             ->with('success', '店舗情報を更新しました。');
        }
    
        /**
         * Remove the specified resource from storage.
         */
        public function destroy(Shop $shop)
        {
            $this->authorize('delete', $shop);
    
            $shop->delete();
    
            return redirect()->route('owner.shops.index')
                             ->with('success', '店舗を削除しました。');
        }
    }
