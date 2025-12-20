<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateStaffProfileRequest;
use App\Models\Shop;
use App\Models\ShopStaff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ShopStaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Shop $shop)
    {
        $staff = $this->getAuthenticatedStaff($shop);

        return view('staff.staffs.index', compact('shop', 'staff'));
    }

    public function edit(Shop $shop)
    {
        $staff = $this->getAuthenticatedStaff($shop);
        $staff->load('profile');

        // パスを完全なURLに変換
        if ($staff->profile?->small_image_url) {
            $staff->profile->small_image_url = Storage::disk('public')->url($staff->profile->small_image_url);
        }
        if ($staff->profile?->large_image_url) {
            $staff->profile->large_image_url = Storage::disk('public')->url($staff->profile->large_image_url);
        }

        return view('staff.staffs.edit', compact('shop', 'staff'));
    }

    public function update(UpdateStaffProfileRequest $request, Shop $shop)
    {
        $staff = $this->getAuthenticatedStaff($shop);
        $validated = $request->validated();
        $profileData = ['nickname' => $validated['nickname']];

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
            if ($profile && $profile->small_image_url) {
                Storage::disk('public')->delete($profile->small_image_url);
            }

            $file = $request->file('small_image');
            $image = Image::read($file)
                ->resize(300, 300, fn ($constraint) => $constraint->aspectRatio())
                ->encode(new JpegEncoder(90));
            $fileName = Str::random(40) . '.jpg';
            $path = 'staff_profiles/small/' . $fileName;
            Storage::disk('public')->put($path, (string) $image);
            $profileData['small_image_url'] = $path;
        }

        // 画像削除処理 (ラージ)
        if ($request->boolean('is_delete_large_image')) {
            if ($profile && $profile->large_image_url) {
                Storage::disk('public')->delete($profile->large_image_url);
            }
            $profileData['large_image_url'] = null;
        }

        if ($request->hasFile('large_image')) {
            if ($profile && $profile->large_image_url) {
                Storage::disk('public')->delete($profile->large_image_url);
            }

            $file = $request->file('large_image');
            $image = Image::read($file)
                ->resize(800, 800, fn ($constraint) => $constraint->aspectRatio())
                ->encode(new JpegEncoder(90));
            $fileName = Str::random(40) . '.jpg';
            $path = 'staff_profiles/large/' . $fileName;
            Storage::disk('public')->put($path, (string) $image);
            $profileData['large_image_url'] = $path;
        }

        $staff->profile()->updateOrCreate(
            ['shop_staff_id' => $staff->id],
            $profileData
        );

        return redirect()->route('staff.staffs.edit', ['shop' => $shop])
            ->with('success', 'プロフィールを更新しました。');
    }

    private function getAuthenticatedStaff(Shop $shop): ShopStaff
    {
        $staff = ShopStaff::where('shop_id', $shop->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return $staff;
    }
}
