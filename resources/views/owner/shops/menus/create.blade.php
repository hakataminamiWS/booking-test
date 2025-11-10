@extends('app')

@section('title', 'メニュー新規登録')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'staffs' => $staffs,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/menus/Create" data-props="{{ json_encode($props) }}">
    </div>
@endsection
