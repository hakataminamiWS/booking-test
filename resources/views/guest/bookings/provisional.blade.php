@extends('app')

@section('title', '仮予約完了')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'booking' => $booking,
            'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="guest/bookings/Provisional" data-props="{{ json_encode($props) }}"></div>
@endsection
