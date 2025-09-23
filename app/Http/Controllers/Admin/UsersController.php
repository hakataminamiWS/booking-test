<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Owner;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    private const USERS_PER_PAGE = 20;

    public function index(Request $request)
    {
        $query = User::with('owner');

        $publicId = $request->input('public_id');
        if ($publicId) {
            $query->where('public_id', 'like', "%{$publicId}%");
        }

        // Sorting
        if ($request->filled('sort_by')) {
            $order = $request->input('sort_order', 'asc');
            $query->orderBy($request->input('sort_by'), $order);
        } else {
            $query->latest(); // Default sort
        }

        $paginator = $query->paginate(self::USERS_PER_PAGE)->withQueryString();

        $users = $paginator->getCollection()->map(function ($user) {
            $user->is_owner = $user->owner()->exists();
            return $user;
        });

        $paginator->setCollection($users);

        return view('admin.users.index', [
            'users' => $paginator,
            'public_id' => $publicId,
        ]);
    }

    public function show(User $user)
    {
        $user->load('owner.contract');

        $isOwner = $user->owner()->exists();
        $contract = $isOwner ? $user->owner->contract : null;
        $hasContract = (bool)$contract;

        return view('admin.users.show', [
            'user' => $user,
            'is_owner' => $isOwner,
            'has_contract' => $hasContract,
            'contract' => $contract,
        ]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'is_owner' => $user->owner()->exists(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $isOwner = $request->boolean('is_owner');

        if ($isOwner) {
            // オーナーにする処理
            if (!$user->owner()->exists()) {
                Owner::create([
                    'user_id' => $user->id,
                    'name' => $user->name ?? $user->public_id, // nameがなければpublic_idを使う
                ]);
                return redirect()->route('admin.users.show', $user)->with('success', 'ユーザーをオーナーに設定しました。');
            }
        } else {
            // オーナーから外す処理
            if ($user->owner()->exists()) {
                $user->owner->delete();
                return redirect()->route('admin.users.show', $user)->with('success', 'オーナーの役割を解除しました。');
            }
        }

        return redirect()->route('admin.users.show', $user)->with('info', '変更はありませんでした。');
    }


    // ... (existing code) ...
}
