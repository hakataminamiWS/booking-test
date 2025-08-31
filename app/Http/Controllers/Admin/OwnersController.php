<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OwnersController extends Controller
{
    public function index()
    {
        $owners = [
            (object)['id' => 1, 'name' => 'オーナーA', 'contract_status' => 'active'],
            (object)['id' => 2, 'name' => 'オーナーB', 'contract_status' => 'inactive'],
        ];
        return view('admin.owners.index', compact('owners'));
    }

    public function show($owner_id)
    {
        $ownerDetails = [
            'id' => $owner_id,
            'name' => "オーナー{$owner_id}",
            'email' => "owner{$owner_id}@example.com",
            'contract_status' => 'active',
            'shops' => [
                ['id' => 101, 'name' => '店舗X'],
                ['id' => 102, 'name' => '店舗Y'],
            ]
        ];
        return view('admin.owners.show', compact('owner_id', 'ownerDetails'));
    }
}