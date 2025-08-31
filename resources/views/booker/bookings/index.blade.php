@extends('app')

@section('title', 'マイ予約')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="booker-bookings-index"
        data-shop-id="{{ $shop_id }}"
        data-bookings='@json($bookings)' {{-- 予約データをJSONで渡す --}}
    >
    </div>
</div>
@endsection
