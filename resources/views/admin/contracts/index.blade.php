@extends('app')

@section('title', '契約管理')

@section('content')
<div class="container">
@php
$props = [
    'contracts' => $contracts,
];
@endphp
    <div
        id="app"
        data-page="admin-contracts-index"
        data-props='@json($props)'
    >
    </div>
</div>
@endsection
