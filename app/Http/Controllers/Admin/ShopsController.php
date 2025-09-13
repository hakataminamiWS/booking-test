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
        // 全ての店舗を取得（statusに関わらず）
        $shops = Shop::all();
        return response()->json(['shops' => $shops]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // バリデーション
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:shops,slug',
            'owner_id' => 'required|exists:users,id',
            'regular_holidays' => 'nullable|array',
            'reservation_acceptance_settings' => 'nullable|array',
            'booking_deadline_minutes' => 'required|integer|min:0',
            'booking_confirmation_type' => 'required|string|in:automatic,manual',
            'status' => 'required|string|max:255',
        ]);

        // owner_idを分離
        $ownerId = $validatedData['owner_id'];
        unset($validatedData['owner_id']);

        $shop = Shop::create($validatedData); // 店舗を保存

        // 店舗とオーナーを紐付け
        $ownerRole = \App\Models\Role::where('name', 'owner')->firstOrFail();
        $shop->users()->attach($ownerId, ['role_id' => $ownerRole->id]);

        return response()->json(['shop' => $shop], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop) // ルートモデルバインディング
    {
        return response()->json(['shop' => $shop]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shop $shop) // ルートモデルバインディング
    {
        // バリデーション
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:shops,slug,' . $shop->id,
            'regular_holidays' => 'nullable|array',
            'reservation_acceptance_settings' => 'nullable|array',
            'booking_deadline_minutes' => 'required|integer|min:0',
            'booking_confirmation_type' => 'required|string|in:automatic,manual',
            'status' => 'required|string|max:255',
        ]);

        $shop->update($validatedData); // 店舗を更新

        return response()->json(['shop' => $shop]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop) // ルートモデルバインディング (論理削除)
    {
        $shop->update(['status' => 'deleting']); // 店舗ステータスをdeletingに変更

        return response()->json(['message' => '店舗が正常に削除手続き中に変更されました。']);
    }

    /**
     * Permanently remove the specified resource from storage.
     */
    public function forceDelete(Shop $shop) // ルートモデルバインディング (物理削除)
    {
        $shop->delete(); // 店舗を物理削除

        return response()->json(['message' => '店舗が正常に物理削除されました。']);
    }
}