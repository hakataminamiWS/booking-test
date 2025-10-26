<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdateShopStaffRequest;
use App\Models\Shop;
use App\Models\ShopStaff;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShopStaffController extends Controller
{
    use AuthorizesRequests;

    public function index(Shop $shop)
    {
        $this->authorize('view', $shop);

        return view('owner.shops.staffs.index', compact('shop'));
    }

    public function edit(Shop $shop, ShopStaff $staff)
    {
        $this->authorize('update', $staff);

        $staff->load('profile');

        return view('owner.shops.staffs.edit', compact('shop', 'staff'));
    }

    public function update(UpdateShopStaffRequest $request, Shop $shop, ShopStaff $staff)
    {
        $validated = $request->validated();

        $staff->profile()->updateOrCreate(
            ['shop_staff_id' => $staff->id],
            [
                'nickname' => $validated['nickname'],
                'small_image_url' => $validated['small_image_url'],
                'large_image_url' => $validated['large_image_url'],
            ]
        );

        return redirect()->route('owner.shops.staffs.index', $shop)
            ->with('success', 'スタッフのプロフィールを更新しました。');
    }
}
