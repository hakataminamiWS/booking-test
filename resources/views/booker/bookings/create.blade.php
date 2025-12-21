@extends('app')

@section('title', '新規予約')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'booker' => $booker,
            'menus' => $menus,
            'staffs' => $staffs,
            'bookings' => $bookings,
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
        ];
    @endphp
    <div id="app" data-page="booker/bookings/Create" data-props="{{ json_encode($props) }}"></div>
@endsection
