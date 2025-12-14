<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreShopBookerRequest;
use App\Http\Requests\Owner\UpdateShopBookerRequest;
use App\Models\Shop;
use App\Models\ShopBooker;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ShopBookerController extends Controller
{
    use AuthorizesRequests;

    public function index(Shop $shop)
    {
        $this->authorize('viewAny', [ShopBooker::class, $shop]);

        return view('owner.shops.bookers.index', ['shop' => $shop]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Shop $shop)
    {
        $this->authorize('create', [ShopBooker::class, $shop]);

        return view('owner.shops.bookers.create', compact('shop'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopBookerRequest $request, Shop $shop)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($shop, $validated) {
            $maxNumber = $shop->bookers()->max('number') ?? 0;

            $bookerData = [
                'number' => $maxNumber + 1,
                'name' => $validated['name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'note_from_booker' => $validated['note_from_booker'] ?? null,
                'user_id' => null, // Assuming guest creation for now
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


        return redirect()->route('owner.shops.bookers.index', $shop)
            ->with('success', '予約者を登録しました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop, ShopBooker $booker)
    {
        $this->authorize('update', $booker);

        $booker->load('crm');

        if (!$booker->crm) {
            $booker->setRelation('crm', new \App\Models\ShopBookerCrm());
        }

        return view('owner.shops.bookers.edit', compact('shop', 'booker'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopBookerRequest $request, Shop $shop, ShopBooker $booker)
    {
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

        return redirect()->route('owner.shops.bookers.index', $shop)
            ->with('success', '予約者情報を更新しました。');
    }
}
