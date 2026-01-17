@extends('app')

@section('title', '予約登録')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'menus' => $menus,
            'staffs' => $staffs,
            'bookers' => $bookers,
            'bookings' => $bookings,
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div
        id="app"
        data-page="owner/shops/bookings/Create"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
