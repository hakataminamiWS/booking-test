@extends('app')

@section('title', '予約一覧')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'menus' => $menus,
            'staffs' => $staffs,
        ];
    @endphp
    <div
        id="app"
        data-page="staff/bookings/Index"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
