@extends('app')

@section('title', '予約キャンセル完了 - ' . $shop->name)

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'booking' => $booking,
            'flashSuccess' => session('success'),
            'flashError' => session('error'),
        ];
    @endphp
    <div
        id="app"
        data-page="guest/bookings/Cancelled"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
