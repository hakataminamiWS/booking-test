@extends('app')

@section('title', 'メニュー新規登録')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'staffs' => $staffs,
            'options' => $options,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/menus/Create" data-props="{{ json_encode($props) }}">
    </div>
@endsection
