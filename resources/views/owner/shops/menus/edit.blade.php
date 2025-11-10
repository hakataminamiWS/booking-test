@extends('app')

@section('title', 'メニュー編集')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'menu' => $menu,
            'staffs' => $staffs,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/menus/Edit" data-props="{{ json_encode($props) }}">
    </div>
@endsection
