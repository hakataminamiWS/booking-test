@extends('app')

@section('title', '予約履歴')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'booker' => $booker,
        ];
    @endphp
    <div id="app" data-page="booker/bookings/Index" data-props="{{ json_encode($props) }}"></div>
@endsection
