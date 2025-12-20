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
            'successMessage' => session('success'),
        ];
    @endphp
    <div id="app" data-page="staff/profile/Edit" data-props="{{ json_encode($props) }}"></div>
@endsection
