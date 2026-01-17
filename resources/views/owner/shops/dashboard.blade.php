@extends('app')

@section('title', 'ダッシュボード | ' . $shop->name)

@section('content')
    <div id="app" data-page="owner/shops/Dashboard" data-props="{{ json_encode([
        'shop' => $shop,
        'bookings' => $bookings,
        'cancellations' => $cancellations,
        'shifts' => $shifts,
        'summary' => $summary,
        'nextArrivalId' => $next_arrival_id,
        'flashSuccess' => session('success'),
        'flashError' => session('error'),
        'flashStatus' => session('status'),
    ]) }}"></div>
@endsection
