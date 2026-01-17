@extends('app')

@section('title', '予約一覧')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'menus' => $menus,
            'staffs' => $staffs,
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div
        id="app"
        data-page="staff/shops/bookings/Index"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
