@extends('app')

@section('title', 'スタッフ登録申し込み')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
        ];
    @endphp
    <div id="app" data-page="staff/applications/Create" data-props='{{ json_encode($props) }}'></div>
@endsection
