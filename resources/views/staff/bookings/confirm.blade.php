@extends('app')

@section('title', '予約確認（スタッフ用）')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="staff-bookings-confirm"
        data-shop-id="{{ $shop_id }}"
        data-booking-details='@json($bookingDetails)'
    >
    </div>
</div>
@endsection
