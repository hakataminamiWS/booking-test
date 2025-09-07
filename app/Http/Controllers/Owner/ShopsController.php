<?php

namespace App\Http\Controllers\Owner;

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
        $shops = auth()->user()->shops; // 認証されたオーナーに関連付けられた店舗のみを取得
        return view('owner.shops.index', compact('shops'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop) // ルートモデルバインディング
    {
        return view('owner.shops.show', compact('shop'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop) // ルートモデルバインディング
    {
        return view('owner.shops.edit', compact('shop'));
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

        return redirect()->route('owner.shops.show', $shop->id)
                         ->with('success', '店舗情報が正常に更新されました。');
    }
}