<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopMenuController extends Controller
{
    /**
     * 指定されたメニューに割り当たっているスタッフのリストを返す
     */
    public function staffs(Request $request, Shop $shop, ShopMenu $menu)
    {
        // TODO: Staff向けの認可チェックが必要であれば追加
        // $this->authorize('viewAny', [ShopStaff::class, $shop]);

        $staffsQuery = $shop->staffs()->with('profile');

        // メニューに割り当たっているスタッフのIDを取得
        $assignedStaffIds = DB::table('shop_menu_staffs')
                              ->where('shop_menu_id', $menu->id)
                              ->pluck('shop_staff_id');

        // 割り当てられているスタッフのみにフィルタリング
        $staffsQuery->whereIn('id', $assignedStaffIds);

        $staffs = $staffsQuery->get()->map(function ($staff) {
            return [
                'id' => $staff->id,
                'profile' => [
                    'nickname' => $staff->profile->nickname ?? '',
                ],
            ];
        });

        return response()->json(['staffs' => $staffs]);
    }
}
