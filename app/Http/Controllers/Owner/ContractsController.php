<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContractsController extends Controller
{
    public function index()
    {
        $contracts = [
            ['id' => 1, 'plan' => 'Basic', 'status' => 'active', 'expires_at' => '2026-12-31'],
            ['id' => 2, 'plan' => 'Premium', 'status' => 'pending', 'expires_at' => '2025-09-30'],
        ];
        return view('owner.contracts.index', compact('contracts'));
    }
}
