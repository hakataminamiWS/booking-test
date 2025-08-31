<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShopsController extends Controller
{
    public function index()
    {
        $shops = [
            ['id' => 1, 'name' => 'オーナーの店舗A', 'status' => 'active'],
            ['id' => 2, 'name' => 'オーナーの店舗B', 'status' => 'inactive'],
        ];
        return view('owner.shops.index', compact('shops'));
    }

    public function show($shop_id)
    {
        $shopDetails = ['id' => $shop_id, 'name' => "オーナーの店舗{$shop_id}", 'address' => '東京都渋谷区', 'phone' => '03-xxxx-xxxx'];
        return view('owner.shops.show', compact('shop_id', 'shopDetails'));
    }
}
