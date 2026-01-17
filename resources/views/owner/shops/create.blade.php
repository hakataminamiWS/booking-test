@extends('app')

@section('title', '店舗の新規登録')

@section('content')
    @php
        $props = [
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
            'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="owner/shops/Create" data-props='{{ json_encode($props) }}'></div>
@endsection