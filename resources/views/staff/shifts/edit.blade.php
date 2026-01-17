@extends('app')

@section('title', 'マイシフト編集')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'staff' => $staff,
            'schedules' => $schedules,
            'businessHours' => $businessHours,
            'specialOpenDays' => $specialOpenDays,
            'specialClosedDays' => $specialClosedDays,
            'date' => $date,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="staff/shops/shifts/Edit" data-props="{{ json_encode($props) }}"></div>
@endsection
