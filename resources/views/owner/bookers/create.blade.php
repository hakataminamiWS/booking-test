@extends('app')

@section('title', '予約者新規登録')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/bookers/Create" data-props="{{ json_encode($props) }}"></div>
@endsection
