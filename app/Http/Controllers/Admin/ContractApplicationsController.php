<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContractApplicationRequest;
use App\Models\ContractApplication;
use Illuminate\Http\Request;

class ContractApplicationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.contract-applications.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(ContractApplication $contractApplication)
    {
        return view('admin.contract-applications.show', compact('contractApplication'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContractApplication $contractApplication)
    {
        return view('admin.contract-applications.edit', compact('contractApplication'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractApplicationRequest $request, ContractApplication $contractApplication)
    {
        $contractApplication->update($request->validated());

        return redirect()->route('admin.contract-applications.show', $contractApplication)
            ->with('success', '契約申し込みのステータスを更新しました。');
    }
}
