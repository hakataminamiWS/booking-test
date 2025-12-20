@extends('app')

@section('title', 'スタッフ一覧')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'currentStaffId' => $staff->id,
            'csrfToken' => csrf_token(),
        ];
    @endphp
    <div id="app" data-page="staff/staffs/Index" data-props="{{ json_encode($props) }}"></div>
@endsection
