<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\Request;

class ContractsController extends Controller
{
    public function index()
    {
        $contracts = Contract::with('user.owner')->get();
        return view('admin.contracts.index', compact('contracts'));
    }

    public function show(Contract $contract)
    {
        $contract->load('user.owner');
        return view('admin.contracts.show', [
            'contract_id' => $contract->id,
            'contractDetails' => $contract
        ]);
    }

    public function create(Request $request)
    {
        $owners = Owner::whereDoesntHave('user.contract')->with('user')->get();
        $selectedUserId = null;

        if ($request->has('user_public_id')) {
            $selectedUserId = $request->user_public_id;
        }

        return view('admin.contracts.create', compact('owners', 'selectedUserId'));
    }

    public function store(Request $request)
    {
        // --- ユーザーID(public_id)の事前検証 ---
        $request->validate(
            ['user_id' => 'required|string|exists:users,public_id'],
            [
                'user_id.required' => 'オーナーを選択してください。',
                'user_id.exists' => '指定されたオーナーが見つかりません。',
            ]
        );

        $user = User::where('public_id', $request->input('user_id'))->firstOrFail();

        // --- 契約の一意性検証 ---
        if (Contract::where('user_id', $user->id)->exists()) {
            return back()->withInput()->withErrors(['user_id' => 'このオーナーには既に契約が存在します。']);
        }

        // --- 残りのフィールドのバリデーション ---
        // クライアントサイドのバリデーションとは別に、サーバーサイドで最終的なデータ整合性を保証するバリデーション。
        $validated = $request->validate([
            'name' => 'required|string',
            'max_shops' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => '契約名は必ず入力してください。',
            'name.string' => '契約名は文字列で入力してください。',
            'max_shops.required' => '最大店舗数は必須です。',
            'max_shops.integer' => '最大店舗数は整数で入力してください。',
            'start_date.required' => '契約開始日は必須です。',
            'start_date.date' => '契約開始日は有効な日付ではありません。',
            'end_date.required' => '契約終了日は必須です。',
            'end_date.date' => '契約終了日は有効な日付ではありません。',
            'end_date.after_or_equal' => '契約終了日は開始日以降の日付にしてください。',
            'status.required' => 'ステータスは必須です。',
        ]);

        // --- 契約作成 ---
        $contractData = array_merge($validated, ['user_id' => $user->id]);
        $contract = Contract::create($contractData);

        return redirect()->route('admin.contracts.show', $contract->id)
            ->with('success', '契約が正常に作成されました。');
    }

    public function edit(Contract $contract)
    {
        $contract->load('user.owner');
        return view('admin.contracts.edit', compact('contract'));
    }

    public function update(Request $request, Contract $contract)
    {
        // クライアントサイドのバリデーションとは別に、サーバーサイドで最終的なデータ整合性を保証するバリデーション。
        $validated = $request->validate([
            'name' => 'required|string',
            'max_shops' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => '契約名は必ず入力してください。',
            'name.string' => '契約名は文字列で入力してください。',
            'max_shops.required' => '最大店舗数は必須です。',
            'max_shops.integer' => '最大店舗数は整数で入力してください。',
            'start_date.required' => '契約開始日は必須です。',
            'start_date.date' => '契約開始日は有効な日付ではありません。',
            'end_date.required' => '契約終了日は必須です。',
            'end_date.date' => '契約終了日は有効な日付ではありません。',
            'end_date.after_or_equal' => '契約終了日は開始日以降の日付にしてください。',
            'status.required' => 'ステータスは必須です。',
        ]);

        $contract->update($validated);

        return redirect()->route('admin.contracts.show', $contract->id)
            ->with('success', '契約情報が更新されました。');
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();

        return redirect()->route('admin.contracts.index')
            ->with('success', '契約が削除されました。');
    }
}
