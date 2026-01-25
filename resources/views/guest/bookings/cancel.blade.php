@extends('app')

@section('title', '予約キャンセル')

@section('content')
    @php
        $props = [
            'booking' => $booking,
            'cancelUrl' => $cancelUrl,
            'flashSuccess' => session('success'),
            'flashError' => session('error'),
        ];
    @endphp
    <div
        id="app"
        data-page="guest/bookings/Cancel"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
