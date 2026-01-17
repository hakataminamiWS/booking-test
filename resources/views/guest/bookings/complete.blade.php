@extends('app')

@section('title', '予約完了')

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
    <div id="app" data-page="guest/bookings/Complete" data-props="{{ json_encode($props) }}"></div>
@endsection
