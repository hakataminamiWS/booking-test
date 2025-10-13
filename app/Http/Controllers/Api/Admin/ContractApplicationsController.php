<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\IndexContractApplicationRequest;
use App\Models\ContractApplication;

class ContractApplicationsController extends Controller
{
    public function index(IndexContractApplicationRequest $request)
    {
        $query = ContractApplication::query();

        // --- Eager load relationships ---
        $query->with(['user', 'contract']);

        // --- Determine necessary joins based on sort and filter ---
        $needsUserJoin = $request->input('sort_by') === 'public_id' || $request->filled('public_id');
        $needsContractJoin = $request->filled('contract_statuses');

        if ($needsUserJoin) {
            $query->leftJoin('users', 'contract_applications.user_id', '=', 'users.id');
        }
        if ($needsContractJoin) {
            $query->leftJoin('contracts', 'contract_applications.id', '=', 'contracts.application_id');
        }

        // Select the columns from the main table to avoid conflicts
        $query->select('contract_applications.*');


        // --- Dynamic Filtering ---
        $filterable = [
            // key => [column, operator]
            'id' => ['contract_applications.id', '='],
            'public_id' => ['users.public_id', '='],
            'customer_name' => ['contract_applications.customer_name', 'like'],
            'statuses' => ['contract_applications.status', 'in'],
        ];

        foreach ($filterable as $key => $options) {
            if ($request->filled($key)) {
                [$column, $operator] = $options;
                $value = $request->input($key);

                if ($operator === 'like') {
                    $query->where($column, 'like', '%' . $value . '%');
                } elseif ($operator === 'in') {
                    $query->whereIn($column, (array) $value);
                } else {
                    $query->where($column, $operator, $value);
                }
            }
        }

        // Special handling for contract_statuses
        if ($request->filled('contract_statuses')) {
            $statuses = (array) $request->input('contract_statuses');
            $query->where(function ($q) use ($statuses) {
                if (in_array('none', $statuses)) {
                    $q->orWhereNull('contracts.id');
                }
                $other_statuses = array_filter($statuses, fn ($s) => $s !== 'none');
                if (!empty($other_statuses)) {
                    $q->orWhereIn('contracts.status', $other_statuses);
                }
            });
        }

        if ($request->filled('created_at_after')) {
            $query->where('contract_applications.created_at', '>=', $request->input('created_at_after'));
        }

        if ($request->filled('created_at_before')) {
            $query->where('contract_applications.created_at', '<=', $request->input('created_at_before'));
        }

        // --- Sorting ---
        if ($request->filled('sort_by')) {
            $sortBy = $request->input('sort_by');
            $orderInput = strtolower($request->input('sort_order', 'asc'));
            $order = in_array($orderInput, ['asc', 'desc']) ? $orderInput : 'asc';

            $sortableColumns = [
                'id' => 'contract_applications.id',
                'customer_name' => 'contract_applications.customer_name',
                'created_at' => 'contract_applications.created_at',
                'public_id' => 'users.public_id',
            ];

            if (array_key_exists($sortBy, $sortableColumns)) {
                $query->orderBy($sortableColumns[$sortBy], $order);
            }
        } else {
            $query->latest('contract_applications.created_at'); // Default sort
        }

        $paginator = $query->paginate($request->input('per_page', 20));

        return response()->json($paginator);
    }
}