@extends('app')

@section('title', '店舗詳細: ' . $shop->name)

@section('content')
    @php
        $props = [
            'shop' => $shop,
        ];
    @endphp
    <div
        id="app"
        data-page="owner-shops-show"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
