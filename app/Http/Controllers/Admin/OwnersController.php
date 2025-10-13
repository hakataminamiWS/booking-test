<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteOwnerRequest;
use App\Http\Requests\Admin\UpdateOwnerRequest;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class OwnersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.owners.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $publicId)
    {
        $user = User::where('public_id', $publicId)->firstOrFail();
        $owner = Owner::where('user_id', $user->id)
                      ->with(['user', 'contracts', 'shops'])
                      ->firstOrFail();

        return view('admin.owners.show', compact('owner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $publicId)
    {
        $user = User::where('public_id', $publicId)->firstOrFail();
        $owner = Owner::where('user_id', $user->id)->with('user')->firstOrFail();

        return view('admin.owners.edit', compact('owner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOwnerRequest $request, string $publicId)
    {
        $user = User::where('public_id', $publicId)->firstOrFail();
        $owner = Owner::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validated();
        $owner->update($validated);

        return Redirect::route('admin.owners.show', ['public_id' => $publicId])
                       ->with('success', 'オーナー情報を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteOwnerRequest $request, string $publicId)
    {
        $user = User::where('public_id', $publicId)->firstOrFail();
        $owner = Owner::where('user_id', $user->id)->withCount(['contracts', 'shops'])->firstOrFail();

        if ($owner->contracts_count > 0 || $owner->shops_count > 0) {
            throw ValidationException::withMessages([
                'delete' => 'このオーナーには契約または店舗が紐付いているため、削除できません。',
            ]);
        }

        $owner->delete();

        return Redirect::route('admin.owners.index')
                       ->with('success', 'オーナーを削除しました。');
    }
}
