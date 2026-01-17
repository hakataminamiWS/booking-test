@extends('app')

@section('title', '予約者一覧')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'errors' => $errors->all(),
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="staff/shops/bookers/Index" data-props="{{ json_encode($props) }}"></div>
@endsection
