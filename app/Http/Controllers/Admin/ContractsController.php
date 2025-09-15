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
            $user = User::where('public_id', $request->user_public_id)->first();
            if ($user) {
                $selectedUserId = $user->id;
            }
        }

        return view('admin.contracts.create', compact('owners', 'selectedUserId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:contracts,user_id',
            'max_shops' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ]);

        $contract = Contract::create($validated);

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
        $validated = $request->validate([
            'max_shops' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
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
