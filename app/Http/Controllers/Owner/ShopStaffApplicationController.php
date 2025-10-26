<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Models\ShopStaffApplication;
use App\Models\ShopStaff;
use App\Models\ShopStaffProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ShopStaffApplicationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Shop $shop)
    {
        // Note: Policyでの認可はVueコンポーネントからのAPIリクエスト時に行うため、ここでは不要
        return view('owner.shops.staff-applications.index', compact('shop'));
    }

    /**
     * Approve the specified staff application.
     */
    public function approve(Shop $shop, ShopStaffApplication $staff_application): RedirectResponse
    {
        $this->authorize('update', $staff_application);

        DB::transaction(function () use ($shop, $staff_application) {
            $staff_application->status = 'approved';
            $staff_application->save();

            // スタッフをshop_staffsテーブルに登録（または更新）
            $staff = ShopStaff::updateOrCreate(
                ['shop_id' => $shop->id, 'user_id' => $staff_application->user_id]
            );

            // スタッフプロフィールをshop_staff_profilesテーブルに登録（または更新）
            ShopStaffProfile::updateOrCreate(
                ['shop_staff_id' => $staff->id],
                ['nickname' => $staff_application->name]
            );
        });

        return redirect()->route('owner.shops.staff-applications.index', ['shop' => $shop])
            ->with('success', '申し込みを承認し、スタッフとして登録しました。');
    }

    /**
     * Reject the specified staff application.
     */
    public function reject(Shop $shop, ShopStaffApplication $staff_application): RedirectResponse
    {
        $this->authorize('update', $staff_application);

        $staff_application->status = 'rejected';
        $staff_application->save();

        return redirect()->route('owner.shops.staff-applications.index', ['shop' => $shop])
            ->with('success', '申し込みを却下しました。');
    }
}
