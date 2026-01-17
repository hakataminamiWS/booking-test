@extends('app')

@section('title', 'プロフィール編集: ' . $staff->profile->nickname)

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
    <div id="app" data-page="owner/shops/staffs/ProfileEdit" data-props="{{ json_encode($props) }}"></div>
@endsection
