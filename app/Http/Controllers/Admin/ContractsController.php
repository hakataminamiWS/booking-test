<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteContractRequest;
use App\Http\Requests\Admin\StoreContractRequest;
use App\Http\Requests\Admin\UpdateContractRequest;
use App\Models\Contract;
use App\Models\ContractApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.contracts.index');
    }

    public function create(Request $request)
    {
        $applicationId = $request->query('application_id');
        if (! $applicationId) {
            abort(400, 'Application ID is required.');
        }

        $application = ContractApplication::with('user')->findOrFail($applicationId);

        return view('admin.contracts.create', compact('application'));
    }

    public function store(StoreContractRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated) {
                // 1. Create Contract
                $contract = Contract::create([
                    'user_id' => $validated['user_id'],
                    'application_id' => $validated['application_id'],
                    'name' => $validated['name'],
                    'max_shops' => $validated['max_shops'],
                    'status' => $validated['status'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                ]);

                // 2. Update Application Status
                $application = ContractApplication::find($validated['application_id']);
                $application->status = 'approved';
                $application->save();
            });
        } catch (\Exception $e) {
            Log::error('Contract creation: Transaction failed.', ['error' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', '契約の作成に失敗しました。'.$e->getMessage());
        }

        return redirect()->route('admin.contracts.index')->with('success', '契約を正常に作成しました。');
    }

    public function show(Contract $contract)
    {
        $contract->load(['user', 'application']);

        return view('admin.contracts.show', compact('contract'));
    }

    public function edit(Contract $contract)
    {
        $contract->load(['user', 'application']);

        return view('admin.contracts.edit', compact('contract'));
    }

    public function update(UpdateContractRequest $request, Contract $contract)
    {
        $validated = $request->validated();
        $contract->update($validated);

        return redirect()->route('admin.contracts.show', $contract)->with('success', '契約情報を更新しました。');
    }

    public function destroy(DeleteContractRequest $request, Contract $contract)
    {
        $contract->delete();

        return redirect()->route('admin.contracts.index')->with('success', '契約を削除しました。');
    }
}
