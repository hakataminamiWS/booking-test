@extends('app')

@section('title', 'ダッシュボード | ' . $shop->name)

@section('content')
    <div id="app" data-page="staff/shops/Dashboard" data-props="{{ json_encode([
        'shop' => $shop,
        'bookings' => $bookings,
        'cancellations' => $cancellations,
        'shifts' => $shifts,
        'summary' => $summary,
        'nextArrivalId' => $next_arrival_id,
        'currentUserStaffId' => $current_user_staff_id,
        'flashSuccess' => session('success'),
        'flashError' => session('error'),
        'flashStatus' => session('status'),
    ]) }}"></div>
@endsection
