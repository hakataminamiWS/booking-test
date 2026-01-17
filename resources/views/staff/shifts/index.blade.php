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
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="staff/shops/shifts/Index" data-props="{{ json_encode($props) }}">
    </div>
@endsection
