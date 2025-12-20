<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreShopBookerRequest;
use App\Http\Requests\Staff\UpdateShopBookerRequest;
use App\Models\Shop;
use App\Models\ShopBooker;
use App\Models\ShopStaff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopBookerController extends Controller
{
    public function index(Shop $shop)
    {
        $this->getAuthenticatedStaff($shop);

        return view('staff.bookers.index', ['shop' => $shop]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Shop $shop)
    {
        $this->getAuthenticatedStaff($shop);

        return view('staff.bookers.create', compact('shop'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopBookerRequest $request, Shop $shop)
    {
        $this->getAuthenticatedStaff($shop);
        $validated = $request->validated();

        DB::transaction(function () use ($shop, $validated) {
            $maxNumber = $shop->bookers()->max('number') ?? 0;

            $bookerData = [
                'number' => $maxNumber + 1,
                'name' => $validated['name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'note_from_booker' => $validated['note_from_booker'] ?? null,
                'user_id' => null,
            ];

            $crmData = [
                'name_kana' => $validated['name_kana'] ?? null,
                'shop_memo' => $validated['shop_memo'] ?? null,
                'last_booking_at' => $validated['last_booking_at'] ?? null,
                'booking_count' => $validated['booking_count'] ?? 0,
            ];

            /** @var ShopBooker $booker */
            $booker = $shop->bookers()->create($bookerData);
            $booker->crm()->create($crmData);
        });

        return redirect()->route('staff.bookers.index', ['shop' => $shop->slug])
            ->with('success', '予約者を登録しました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop, ShopBooker $booker)
    {
        $this->getAuthenticatedStaff($shop);

        $booker->load('crm');

        if (!$booker->crm) {
            $booker->setRelation('crm', new \App\Models\ShopBookerCrm());
        }

        return view('staff.bookers.edit', compact('shop', 'booker'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopBookerRequest $request, Shop $shop, ShopBooker $booker)
    {
        $this->getAuthenticatedStaff($shop);
        $validated = $request->validated();

        DB::transaction(function () use ($booker, $validated) {
            $bookerData = [
                'name' => $validated['name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'note_from_booker' => $validated['note_from_booker'] ?? null,
            ];

            $crmData = [
                'name_kana' => $validated['name_kana'] ?? null,
                'shop_memo' => $validated['shop_memo'] ?? null,
                'last_booking_at' => $validated['last_booking_at'] ?? null,
                'booking_count' => $validated['booking_count'] ?? 0,
            ];

            $booker->update($bookerData);
            $booker->crm()->updateOrCreate([], $crmData);
        });

        return redirect()->route('staff.bookers.index', ['shop' => $shop->slug])
            ->with('success', '予約者情報を更新しました。');
    }

    private function getAuthenticatedStaff(Shop $shop): ShopStaff
    {
        $staff = ShopStaff::where('shop_id', $shop->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return $staff;
    }
}
