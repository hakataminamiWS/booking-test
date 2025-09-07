<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;

class StaffController extends Controller
{
    public function index(Shop $shop)
    {
        return $shop->users()->where('role', 'staff')->get();
    }
}
