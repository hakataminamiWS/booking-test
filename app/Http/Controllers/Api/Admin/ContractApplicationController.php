<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractApplication;
use Illuminate\Http\Request;

class ContractApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = ContractApplication::with(['user', 'contract']);

        // --- Dynamic Filtering ---
        $filterable = [
            'id' => 'exact',
            'customer_name' => 'like',
            'public_id' => 'exact',
            'statuses' => 'in',
            'contract_statuses' => 'contract_in',
        ];

        foreach ($filterable as $key => $operator) {
            if ($request->filled($key)) {
                $value = $request->input($key);
                switch ($operator) {
                    case 'like':
                        $query->where($key, 'like', '%' . $value . '%');
                        break;
                    case 'exact':
                        if ($key === 'public_id') {
                            $query->whereHas('user', function ($q) use ($value) {
                                $q->where('public_id', $value);
                            });
                        } else {
                            $query->where($key, $value);
                        }
                        break;
                    case 'in':
                        $query->whereIn('status', (array) $value);
                        break;
                    case 'contract_in':
                        $statuses = (array) $value;
                        $query->where(function ($q) use ($statuses) {
                            if (in_array('none', $statuses)) {
                                $q->doesntHave('contract');
                            }
                            $other_statuses = array_filter($statuses, fn ($s) => $s !== 'none');
                            if (!empty($other_statuses)) {
                                $q->orWhereHas('contract', function ($cq) use ($other_statuses) {
                                    $cq->whereIn('status', $other_statuses);
                                });
                            }
                        });
                        break;
                }
            }
        }

        if ($request->filled('created_at_after')) {
            $query->where('created_at', '>=', $request->input('created_at_after'));
        }

        if ($request->filled('created_at_before')) {
            $query->where('created_at', '<=', $request->input('created_at_before'));
        }

        // --- Sorting ---
        if ($request->filled('sort_by')) {
            $order = $request->input('sort_order', 'asc');
            $query->orderBy($request->input('sort_by'), $order);
        } else {
            $query->latest(); // Default sort
        }

        $paginator = $query->paginate($request->input('per_page', 20));

        return response()->json($paginator);
    }
}