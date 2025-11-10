@extends('app')

@section('title', '契約詳細')

@section('content')
    @php
        $props = ['contract' => $contract];
    @endphp
    <div
        id="app"
        data-page="admin/contracts/Show"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
