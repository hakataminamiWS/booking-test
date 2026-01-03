@extends('app')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'booking' => $booking,
        ];
    @endphp
    <div id="app" data-page="guest/bookings/Complete" data-props="{{ json_encode($props) }}"></div>
@endsection
