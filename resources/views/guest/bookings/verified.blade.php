@extends('app')

@section('title', '予約確定完了 - ' . $shop->name)

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'booking' => $booking,
        ];
    @endphp
    {{-- Complete.vue を再利用 --}}
    <div id="app" data-page="guest/bookings/Complete" data-props="{{ json_encode($props) }}"></div>
@endsection
