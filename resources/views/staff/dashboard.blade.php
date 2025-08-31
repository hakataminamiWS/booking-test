@extends('app')

@section('title', 'スタッフダッシュボード')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="staff-dashboard"
        data-shop-id="{{ $shop_id }}"
        data-dashboard-data='@json($dashboardData)'
    >
    </div>
</div>
@endsection