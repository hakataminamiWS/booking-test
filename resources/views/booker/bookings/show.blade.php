@extends('app')

@section('title', '予約詳細')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'booker' => $booker,
            'booking' => $booking,
            'csrfToken' => csrf_token(),
            'flashSuccess' => session('success'),
            'errorMessage' => session('error'),
            'cancellationDeadlineMinutes' => $cancellationDeadlineMinutes ?? 1440,
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="booker/bookings/Show" data-props="{{ json_encode($props) }}"></div>
@endsection
