@extends('app')

@section('title', '予約編集')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'booking' => $booking,
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
        data-page="staff/shops/bookings/Edit"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
