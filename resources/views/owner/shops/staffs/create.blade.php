@extends('app')

@section('title', '予約枠用スタッフ登録')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/staffs/Create" data-props="{{ json_encode($props) }}"></div>
@endsection
