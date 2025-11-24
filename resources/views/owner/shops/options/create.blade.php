@extends('app')

@section('title', 'オプション新規登録')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/options/Create" data-props="{{ json_encode($props) }}">
    </div>
@endsection
