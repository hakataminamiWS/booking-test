@extends('app')

@section('title', 'メニュー一覧')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'errors' => $errors->all(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/menus/Index" data-props="{{ json_encode($props) }}">
    </div>
@endsection
