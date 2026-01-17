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
        // パスを完全なURLに変換
        if ($staff->profile?->image_url) {
            $staff->profile->image_url = Storage::disk('public')->url($staff->profile->image_url);
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
        // 画像削除処理
        if ($request->boolean('is_delete_image')) {
            if ($profile && $profile->image_url) {
                Storage::disk('public')->delete($profile->image_url);
            }
            $profileData['image_url'] = null;
        }

        if ($request->hasFile('image')) {
            if ($profile && $profile->image_url) {
                Storage::disk('public')->delete($profile->image_url);
            }

            $file = $request->file('image');
            $image = Image::read($file)
                ->resize(300, 300, fn ($constraint) => $constraint->aspectRatio())
                ->encode(new JpegEncoder(90));
            $fileName = Str::random(40) . '.jpg';
            $path = 'staff_profiles/' . $fileName;
            Storage::disk('public')->put($path, (string) $image);
            $profileData['image_url'] = $path;
        }

        // 画像削除処理 (ラージ)


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
