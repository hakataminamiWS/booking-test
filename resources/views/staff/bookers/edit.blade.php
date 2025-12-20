@extends('app')

@section('title', '予約者編集')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'booker' => $booker,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
        ];
    @endphp
    <div id="app" data-page="staff/bookers/Edit" data-props="{{ json_encode($props) }}"></div>
@endsection
