@extends('app')

@section('title', 'シフト一覧')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'targetMonth' => $targetMonth->toDateString(),
            'staffs' => $staffs,
            'weeksWithShiftStatus' => $weeksWithShiftStatus,
        ];
    @endphp
    <div id="app" data-page="owner/shops/staffs/shifts/Index" data-props="{{ json_encode($props) }}">
    </div>
@endsection
