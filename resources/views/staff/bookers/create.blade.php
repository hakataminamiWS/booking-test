@extends('app')

@section('title', '予約者新規登録')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="staff/shops/bookers/Create" data-props="{{ json_encode($props) }}"></div>
@endsection
