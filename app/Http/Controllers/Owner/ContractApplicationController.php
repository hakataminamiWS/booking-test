<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreContractApplicationRequest;
use App\Models\ContractApplication;
use Illuminate\Support\Facades\Auth;

class ContractApplicationController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // TODO: SNSログイン機能の実装時に、emailも渡すように修正する
        $props = [
            'publicId' => $user->public_id,
        ];

        return view('owner.contract.apply', compact('props'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContractApplicationRequest $request)
    {
        // バリデーション済みのデータを取得
        $validated = $request->validated();

        // 取得したデータをDBに保存する
        ContractApplication::create([
            'user_id' => Auth::id(),
            'customer_name' => $validated['customer_name'],
        ]);

        // 完了メッセージと共にダッシュボードなどへリダイレクト
        return redirect()->route('owner.dashboard')->with('status', '契約の申し込みが完了しました。管理者からの連絡をお待ちください。');
    }
}