<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdateShopStaffRequest;
use App\Models\Shop;
use App\Models\ShopStaff;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;

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
        $profileData = ['nickname' => $validated['nickname']];

        if ($request->hasFile('small_image')) {
            // 古い画像を削除
            if ($staff->profile && $staff->profile->small_image_url) {
                $oldPath = str_replace('/storage/', '', parse_url($staff->profile->small_image_url, PHP_URL_PATH));
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('small_image');
            $image = Image::read($file)
                ->resize(300, 300, fn ($constraint) => $constraint->aspectRatio())
                ->encode(new JpegEncoder(80));
            $fileName = Str::random(40) . '.jpeg';
            $path = 'staff_profiles/small/' . $fileName;
            Storage::disk('public')->put($path, (string) $image);
            $profileData['small_image_url'] = Storage::url($path);
        }

        if ($request->hasFile('large_image')) {
            // 古い画像を削除
            if ($staff->profile && $staff->profile->large_image_url) {
                $oldPath = str_replace('/storage/', '', parse_url($staff->profile->large_image_url, PHP_URL_PATH));
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('large_image');
            $image = Image::read($file)
                ->resize(800, 800, fn ($constraint) => $constraint->aspectRatio())
                ->encode(new JpegEncoder(80));
            $fileName = Str::random(40) . '.jpeg';
            $path = 'staff_profiles/large/' . $fileName;
            Storage::disk('public')->put($path, (string) $image);
            $profileData['large_image_url'] = Storage::url($path);
        }

        $staff->profile()->updateOrCreate(
            ['shop_staff_id' => $staff->id],
            $profileData
        );

        return redirect()->route('owner.shops.staffs.edit', ['shop' => $shop, 'staff' => $staff])
            ->with('success', 'スタッフのプロフィールを更新しました。');
    }
}
