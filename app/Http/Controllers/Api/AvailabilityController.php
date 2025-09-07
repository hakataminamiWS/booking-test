<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Shop;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(private AvailabilityService $availabilityService)
    {
    }

    public function index(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'menu_id' => ['required', 'exists:menus,id'],
        ]);

        $menu = Menu::find($validated['menu_id']);

        $availableSlots = $this->availabilityService->getAvailableSlots(
            $shop,
            $menu,
            $validated['date']
        );

        return response()->json($availableSlots);
    }
}
