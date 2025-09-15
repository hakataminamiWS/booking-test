@extends('app')

@section('title', 'ユーザー詳細')

@section('content')
<div class="container">
@php
$props = [
    'user' => $user,
    'isOwner' => $is_owner,
    'hasContract' => $has_contract,
    'addActionUrl' => route('admin.users.roles.add', ['user' => $user]),
    'removeActionUrl' => route('admin.users.roles.remove', ['user' => $user]),
    'createContractUrl' => route('admin.contracts.create', ['user_public_id' => $user->public_id]),
    'csrfToken' => csrf_token(),
];
@endphp
    <div
        id="app"
        data-page="admin-users-show"
        data-props="{{ json_encode($props) }}"
    >
    </div>@endsection
