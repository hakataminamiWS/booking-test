<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContractsController extends Controller
{
    public function index()
    {
        $contracts = [
            ['id' => 1, 'owner_name' => 'オーナーA', 'plan' => 'Basic', 'status' => 'active', 'expires_at' => '2026-12-31'],
            ['id' => 2, 'owner_name' => 'オーナーB', 'plan' => 'Premium', 'status' => 'pending', 'expires_at' => '2025-09-30'],
        ];
        return view('admin.contracts.index', compact('contracts'));
    }

    public function show($contract_id)
    {
        $contractDetails = [
            'id' => $contract_id,
            'owner_name' => 'オーナーA',
            'plan' => 'Basic',
            'status' => 'active',
            'expires_at' => '2026-12-31',
            'notes' => '特記事項など。',
        ];
        return view('admin.contracts.show', compact('contract_id', 'contractDetails'));
    }
}
