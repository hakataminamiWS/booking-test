@extends('app')

@section('title', '予約一覧（スタッフ）')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="staff-bookings-index"
        data-shop-id="{{ $shop_id }}"
        data-bookings='@json($bookings)' {{-- 予約データをJSONで渡す --}}
    >
    </div>
</div>
@endsection
