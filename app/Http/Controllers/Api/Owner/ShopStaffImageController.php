<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\StoreLargeShopStaffImageRequest;
use App\Http\Requests\Api\Owner\StoreSmallShopStaffImageRequest;
use App\Models\Shop;
use App\Models\ShopStaff;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class ShopStaffImageController extends Controller
{
    use AuthorizesRequests;

    public function storeSmallImage(StoreSmallShopStaffImageRequest $request, Shop $shop, ShopStaff $staff): JsonResponse
    {
        $this->authorize('update', $staff);

        $file = $request->file('image');

        $file = $request->file('image');

        // 画像をサニタイズしてWebP形式で再エンコード (小サイズ用)
        $image = Image::make($file)
                      ->resize(300, 300, function ($constraint) {
                          $constraint->aspectRatio();
                      })
                      ->encode('webp', 80);

        $fileName = Str::random(40) . '.webp';
        $path = 'staff_profiles/small/' . $fileName;

        Storage::disk('public')->put($path, (string) $image);

        if ($staff->profile && $staff->profile->small_image_path) {
            Storage::disk('public')->delete($staff->profile->small_image_path);
        }

        $staff->profile()->updateOrCreate(
            ['shop_staff_id' => $staff->id],
            ['small_image_path' => $path]
        );

        return response()->json([
            'url' => Storage::url($path),
            'path' => $path,
        ]);
    }

    public function destroySmallImage(Shop $shop, ShopStaff $staff): JsonResponse
    {
        $this->authorize('update', $staff);

        if ($staff->profile && $staff->profile->small_image_path) {
            Storage::disk('public')->delete($staff->profile->small_image_path);

            $staff->profile()->updateOrCreate(
                ['shop_staff_id' => $staff->id],
                ['small_image_path' => null]
            );
        }

        return response()->json(null, 204);
    }

    public function storeLargeImage(StoreLargeShopStaffImageRequest $request, Shop $shop, ShopStaff $staff): JsonResponse
    {
        $this->authorize('update', $staff);

        $file = $request->file('image');

        // 画像をサニタイズしてWebP形式で再エンコード (大サイズ用)
        $image = Image::make($file)
                      ->resize(800, 800, function ($constraint) {
                          $constraint->aspectRatio();
                      })
                      ->encode('webp', 80);

        $fileName = Str::random(40) . '.webp';
        $path = 'staff_profiles/large/' . $fileName;

        Storage::disk('public')->put($path, (string) $image);

        if ($staff->profile && $staff->profile->large_image_path) {
            Storage::disk('public')->delete($staff->profile->large_image_path);
        }

        $staff->profile()->updateOrCreate(
            ['shop_staff_id' => $staff->id],
            ['large_image_path' => $path]
        );

        return response()->json([
            'url' => Storage::url($path),
            'path' => $path,
        ]);
    }

    public function destroyLargeImage(Shop $shop, ShopStaff $staff): JsonResponse
    {
        $this->authorize('update', $staff);

        if ($staff->profile && $staff->profile->large_image_path) {
            Storage::disk('public')->delete($staff->profile->large_image_path);

            $staff->profile()->updateOrCreate(
                ['shop_staff_id' => $staff->id],
                ['large_image_path' => null]
            );
        }

        return response()->json(null, 204);
    }
}
