@extends('app')

@section('title', '契約詳細')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="admin-contracts-show"
        data-contract-id="{{ $contract_id }}"
        data-contract-details='@json($contractDetails)'
    >
    </div>
</div>
@endsection
