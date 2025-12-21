<?php

namespace App\Http\Controllers\Api\Booker;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopMenuController extends Controller
{
    /**
     * Get the list of staffs assigned to a specific menu.
     */
    public function staffs(Request $request, Shop $shop, ShopMenu $menu)
    {
        // Get IDs of staffs assigned to the menu
        $assignedStaffIds = DB::table('shop_menu_staffs')
                              ->where('shop_menu_id', $menu->id)
                              ->pluck('shop_staff_id');

        // Fetch staff details (nickname) for those IDs within the shop
        $staffs = $shop->staffs()
            ->with('profile')
            ->whereIn('id', $assignedStaffIds)
            ->get()
            ->map(function ($staff) {
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
