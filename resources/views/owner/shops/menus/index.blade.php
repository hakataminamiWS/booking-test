@extends('app')

@section('title', 'メニュー一覧')

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
    <div id="app" data-page="owner/shops/menus/Index" data-props="{{ json_encode($props) }}">
    </div>
@endsection
