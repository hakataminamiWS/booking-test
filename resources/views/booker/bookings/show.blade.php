@extends('app')

@section('title', '予約詳細')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="booker-bookings-show"
        data-shop-id="{{ $shop_id }}"
        data-booking-id="{{ $booking_id }}"
        data-booking-details='@json($bookingDetails)'
    >
    </div>
</div>
@endsection
