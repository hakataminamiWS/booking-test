@extends('app')

@section('title', '予約内容の確認')

@section('content')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>予約確認</title>
    <div id="app"
         data-page="booking-confirm"
         data-action="{{ route('booker.bookings.store') }}"
         data-booking-data='{{ json_encode($bookingData) }}'
    ></div>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body>
</body>
</html>

@endsection
