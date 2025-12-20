<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreReservationFrameStaffRequest;
use App\Http\Requests\Owner\UpdateShopStaffRequest;
use App\Models\Shop;
use App\Models\ShopStaff;
use App\Models\ShopStaffProfile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ShopStaffController extends Controller
{
    use AuthorizesRequests;

    public function index(Shop $shop)
    {
        $this->authorize('viewAny', [ShopStaff::class, $shop]);

        return view('owner.shops.staffs.index', compact('shop'));
    }

    public function edit(Shop $shop, ShopStaff $staff)
    {
        $this->authorize('update', $staff);

        $staff->load('profile');

        // パスを完全なURLに変換
        if ($staff->profile?->small_image_url) {
            $staff->profile->small_image_url = Storage::disk('public')->url($staff->profile->small_image_url);
        }
        if ($staff->profile?->large_image_url) {
            $staff->profile->large_image_url = Storage::disk('public')->url($staff->profile->large_image_url);
        }

        return view('owner.shops.staffs.edit', compact('shop', 'staff'));
    }

    public function update(UpdateShopStaffRequest $request, Shop $shop, ShopStaff $staff)
    {
        $validated = $request->validated();
        $profileData = ['nickname' => $validated['nickname']];

        // プロフィールを先にロードしておく
        $staff->load('profile');
        $profile = $staff->profile;

        // 画像削除処理 (スモール)
        if ($request->boolean('is_delete_small_image')) {
            if ($profile && $profile->small_image_url) {
                Storage::disk('public')->delete($profile->small_image_url);
            }
            $profileData['small_image_url'] = null;
        }

        if ($request->hasFile('small_image')) {
            // 古い画像を削除
            if ($profile && $profile->small_image_url) {
                Storage::disk('public')->delete($profile->small_image_url);
            }

            $file = $request->file('small_image');
            $image = Image::read($file)
                ->resize(300, 300, fn ($constraint) => $constraint->aspectRatio())
                ->encode(new JpegEncoder(90)); // 品質を90に変更
            $fileName = Str::random(40) . '.jpg'; // 拡張子を.jpgに変更
            $path = 'staff_profiles/small/' . $fileName;
            Storage::disk('public')->put($path, (string) $image);
            $profileData['small_image_url'] = $path; // URLではなくパスを保存
        }

        // 画像削除処理 (ラージ)
        if ($request->boolean('is_delete_large_image')) {
            if ($profile && $profile->large_image_url) {
                Storage::disk('public')->delete($profile->large_image_url);
            }
            $profileData['large_image_url'] = null;
        }

        if ($request->hasFile('large_image')) {
            // 古い画像を削除
            if ($profile && $profile->large_image_url) {
                Storage::disk('public')->delete($profile->large_image_url);
            }

            $file = $request->file('large_image');
            $image = Image::read($file)
                ->resize(800, 800, fn ($constraint) => $constraint->aspectRatio())
                ->encode(new JpegEncoder(90)); // 品質を90に変更
            $fileName = Str::random(40) . '.jpg'; // 拡張子を.jpgに変更
            $path = 'staff_profiles/large/' . $fileName;
            Storage::disk('public')->put($path, (string) $image);
            $profileData['large_image_url'] = $path; // URLではなくパスを保存
        }

        $staff->profile()->updateOrCreate(
            ['shop_staff_id' => $staff->id],
            $profileData
        );

        return redirect()->route('owner.shops.staffs.edit', ['shop' => $shop, 'staff' => $staff])
            ->with('success', 'スタッフのプロフィールを更新しました。');
    }

    public function create(Shop $shop)
    {
        $this->authorize('create', [ShopStaff::class, $shop]);

        return view('owner.shops.staffs.create', compact('shop'));
    }

    public function store(StoreReservationFrameStaffRequest $request, Shop $shop)
    {

        $validated = $request->validated();

        DB::transaction(function () use ($shop, $validated) {
            $shopStaff = ShopStaff::create([
                'shop_id' => $shop->id,
                'user_id' => null, // 予約枠用スタッフなのでuser_idはnull
            ]);

            ShopStaffProfile::create([
                'shop_staff_id' => $shopStaff->id,
                'nickname' => $validated['nickname'],
            ]);
        });

        return redirect()->route('owner.shops.staffs.index', ['shop' => $shop])
            ->with('success', '予約枠用スタッフを登録しました。');
    }
}
