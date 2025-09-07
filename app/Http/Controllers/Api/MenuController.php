<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;

class MenuController extends Controller
{
    public function index(Shop $shop)
    {
        return $shop->menus;
    }
}
