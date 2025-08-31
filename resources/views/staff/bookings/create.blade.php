@extends('app')

@section('title', '予約作成（スタッフ用）')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="staff-bookings-create"
        data-shop-id="{{ $shop_id }}"
        data-action="{{ route('staff.bookings.store', ['shop_id' => $shop_id]) }}"
    >
    </div>
</div>
@endsection
