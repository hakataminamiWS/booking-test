@extends('app')

@section('title', '予約者一覧')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'errors' => $errors->all(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/bookers/Index" data-props="{{ json_encode($props) }}"></div>
@endsection
