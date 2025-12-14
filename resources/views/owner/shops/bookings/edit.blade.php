@extends('app')

@section('title', '予約編集')

@section('content')
    @php
        /* 
         * Edit.vue の props 定義:
         * shop: Shop
         * booking: Booking (with relations)
         * menus: Menu[]
         * staffs: Staff[]
         * bookers: Booker[]
         * bookings: Booking[] (other bookings for calendar)
         */
        $props = [
            'shop' => $shop,
            'booking' => $booking,
            'menus' => $menus,
            'staffs' => $staffs,
            'bookers' => $bookers,
            'bookings' => $bookings,
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
        ];
    @endphp
    <div
        id="app"
        data-page="owner/shops/bookings/Edit"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
