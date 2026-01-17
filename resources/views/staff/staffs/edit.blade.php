@extends('app')

@section('title', 'マイプロフィール編集')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'staff' => $staff,
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
            'flashSuccess' => session('success'),
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="staff/profile/Edit" data-props="{{ json_encode($props) }}"></div>
@endsection
