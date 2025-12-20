@extends('app')

@section('title', 'シフト一覧')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'targetMonth' => $targetMonth->toDateString(),
            'staffs' => $allStaffs,
            'weeksWithShiftStatus' => $weeksWithShiftStatus,
            'currentStaffId' => $currentStaffId,
        ];
    @endphp
    <div id="app" data-page="staff/shifts/Index" data-props="{{ json_encode($props) }}">
    </div>
@endsection
