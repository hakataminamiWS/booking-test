@extends('app')

@section('title', 'ゲスト予約')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'menus' => $menus,
            'staffs' => $staffs,
            'bookings' => $bookings,
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="guest/bookings/Create" data-props="{{ json_encode($props) }}"></div>
@endsection
