@extends('app')

@section('title', '予約可能枠管理')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="staff-schedules-index"
        data-shop-id="{{ $shop_id }}"
        data-schedule-data='@json($scheduleData)'
        data-action="{{ route('staff.schedules.store', ['shop_id' => $shop_id]) }}"
    >
    </div>
</div>
@endsection
