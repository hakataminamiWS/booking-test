@extends('app')

@section('title', '特別休業日登録')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
        ];
    @endphp
    <div id="app" data-page="owner-shops-business-hours-special-closed-days-create" data-props="{{ json_encode($props) }}"></div>
@endsection
