@extends('app')

@section('title', '仮予約完了')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'booker' => $booker,
            'booking' => $booking,
            'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="booker/bookings/Provisional" data-props="{{ json_encode($props) }}"></div>
@endsection
