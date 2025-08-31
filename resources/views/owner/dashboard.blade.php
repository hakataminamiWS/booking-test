@extends('app')

@section('title', 'オーナーダッシュボード')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="owner-dashboard"
        data-dashboard-data='@json($dashboardData)'
    >
    </div>
</div>
@endsection