<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;

class ShopsController extends Controller
{
    /**
     * Display a listing of shops the booker is registered with.
     */
    public function index()
    {
        return view('booker.shops.index');
    }
}
