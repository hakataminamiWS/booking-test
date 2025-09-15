@extends('app')

@section('title', '契約情報編集')

@section('content')
<div class="container">
@php
$props = [
    'contract' => $contract,
    'updateUrl' => route('admin.contracts.update', $contract),
    'csrfToken' => csrf_token(),
    'errors' => $errors->toArray(),
    'oldInput' => session()->getOldInput(),
];
@endphp
    <div
        id="app"
        data-page="admin-contracts-edit"
        data-props="{{ json_encode($props) }}"
    >
    </div>
</div>
@endsection
