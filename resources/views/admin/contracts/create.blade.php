@extends('app')

@section('title', '新規契約作成')

@section('content')
<div class="container">
@php
$props = [
    'owners' => $owners,
    'selectedUserId' => $selectedUserId ?? null,
    'storeUrl' => route('admin.contracts.store'),
    'csrfToken' => csrf_token(),
    'errors' => $errors->toArray(),
    'oldInput' => session()->getOldInput(),
];
@endphp
    <div
        id="app"
        data-page="admin-contracts-create"
        data-props="{{ json_encode($props) }}"
    >
    </div>
</div>
@endsection
