@extends('app')

@section('title', '予約完了')

@section('content')
<div
    id="app"
    data-page="booking-complete"
    data-status="{{ session('status', '') }}"
    data-home-url="{{ url('/') }}"
></div>
@endsection