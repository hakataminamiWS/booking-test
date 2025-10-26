<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreApplicationRequest;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function create(Shop $shop)
    {
        // Authorization is handled in StoreApplicationRequest and future policies
        return view('staff.applications.create', compact('shop'));
    }

    public function complete()
    {
        return view('staff.applications.complete');
    }

    public function store(StoreApplicationRequest $request, Shop $shop)
    {
        $shop->staffApplications()->create([
            'user_id' => Auth::id(),
            'name' => $request->input('name'),
            'status' => 'pending',
        ]);

        return redirect()->route('staff.application.complete')
            ->with('success', '店舗スタッフへの申し込みが完了しました。オーナーからの承認をお待ちください。');
    }
}
