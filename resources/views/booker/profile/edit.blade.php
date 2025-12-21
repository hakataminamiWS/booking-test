@extends('app')

@section('title', 'プロフィール編集')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'booker' => $booker,
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
            'successMessage' => session('success'),
        ];
    @endphp
    <div id="app" data-page="booker/profile/Edit" data-props="{{ json_encode($props) }}"></div>
@endsection
