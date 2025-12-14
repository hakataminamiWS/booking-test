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
        ];
    @endphp
    <div
        id="app"
        data-page="owner/shops/bookings/Edit"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
