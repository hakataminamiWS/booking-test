@extends('app')

@section('title', '会員情報登録')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'user' => $user,
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="booker/profile/Create" data-props="{{ json_encode($props) }}"></div>
@endsection
