@extends('app')

@section('title', '営業時間一覧')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'businessHours' => $businessHours,
            'specialOpenDays' => $specialOpenDays,
            'specialClosedDays' => $specialClosedDays,
        ];
    @endphp
    <div
        id="app"
        data-page="owner/shops/business-hours/Index"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
