@extends('app')

@section('title', '契約申し込み詳細')

@section('content')
    @php
        $props = ['contractApplication' => $contractApplication->load('user')];
    @endphp
    <div
        id="app"
        data-page="admin/contract-applications/Show"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
