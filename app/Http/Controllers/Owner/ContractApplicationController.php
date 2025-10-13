<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreContractApplicationRequest;
use App\Models\ContractApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();

        try {
            // バリデーション済みのデータを取得
            $validated = $request->validated();

            // 取得したデータをDBに保存する
            $application = ContractApplication::create([
                'user_id' => Auth::id(),
                'customer_name' => $validated['customer_name'],
                'email' => $validated['email'],
            ]);

            DB::commit();

            // 完了メッセージと共にトップページなどへリダイレクト
            return redirect('/')->with('status', '契約の申し込みが完了しました。管理者からの連絡をお待ちください。');
        } catch (\Exception $e) {
            DB::rollBack();
            // エラーログの出力や、適切なエラーレスポンスを返す
            \Illuminate\Support\Facades\Log::error('Contract application store failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create application'], 500);
        }
    }
}