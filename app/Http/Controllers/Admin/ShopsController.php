<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 全ての店舗（論理削除されたものも含む）を取得
        $shops = Shop::withTrashed()->get();
        return view('admin.shops.index', compact('shops'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.shops.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // バリデーション
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i|after:opening_time',
            'regular_holidays' => 'nullable|array',
            'regular_holidays.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'reservation_acceptance_settings' => 'nullable|array',
        ]);

        $shop = Shop::create($validatedData); // 店舗を保存

        return redirect()->route('admin.shops.show', $shop->id)
                         ->with('success', '店舗が正常に登録されました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop) // ルートモデルバインディング
    {
        return view('admin.shops.show', compact('shop'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop) // ルートモデルバインディング
    {
        return view('admin.shops.edit', compact('shop'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shop $shop) // ルートモデルバインディング
    {
        // バリデーション
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i|after:opening_time',
            'regular_holidays' => 'nullable|array',
            'regular_holidays.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'reservation_acceptance_settings' => 'nullable|array',
        ]);

        $shop->update($validatedData); // 店舗を更新

        return redirect()->route('admin.shops.show', $shop->id)
                         ->with('success', '店舗情報が正常に更新されました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop) // ルートモデルバインディング (論理削除)
    {
        $shop->delete(); // 店舗を論理削除

        return redirect()->route('admin.shops.index')
                         ->with('success', '店舗が正常に論理削除されました。');
    }

    /**
     * Permanently remove the specified resource from storage.
     */
    public function forceDelete(Shop $shop) // ルートモデルバインディング (物理削除)
    {
        $shop->forceDelete(); // 店舗を物理削除

        return redirect()->route('admin.shops.index')
                         ->with('success', '店舗が正常に物理削除されました。');
    }
}