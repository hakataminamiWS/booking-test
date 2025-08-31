@extends('app')

@section('title', '予約完了')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="booker-bookings-complete"
        data-shop-id="{{ $shop_id }}"
        data-booking-details='@json($bookingDetails)' {{-- コントローラーから渡された予約詳細 --}}
    >
    </div>
</div>

@endsection
