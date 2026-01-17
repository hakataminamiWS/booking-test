@extends('app')

@section('title', '店舗詳細: ' . $shop->name)

@section('content')
    @php
        $props = [
            'shop' => $shop,
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div
        id="app"
        data-page="owner/shops/Show"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
