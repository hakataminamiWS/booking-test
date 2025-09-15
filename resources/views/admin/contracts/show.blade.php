@extends('app')

@section('title', '契約詳細')

@section('content')
<div class="container">
@php
$props = [
    'contractId' => $contract_id,
    'contractDetails' => $contractDetails,
    'csrfToken' => csrf_token(),
];
@endphp
    <div
        id="app"
        data-page="admin-contracts-show"
        data-props='@json($props)'
    >
    </div>
</div>
@endsection
