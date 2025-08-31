<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index($shop_id)
    {
        $staffs = [
            ['id' => 1, 'name' => 'スタッフA', 'role' => 'スタイリスト'],
            ['id' => 2, 'name' => 'スタッフB', 'role' => 'アシスタント'],
        ];
        return view('owner.shops.staff.index', compact('shop_id', 'staffs'));
    }

    public function edit($shop_id, $staff_id)
    {
        $staffDetails = ['id' => $staff_id, 'name' => "スタッフ{$staff_id}", 'role' => 'スタイリスト', 'email' => 'staff@example.com'];
        return view('owner.shops.staff.edit', compact('shop_id', 'staff_id', 'staffDetails'));
    }
}
