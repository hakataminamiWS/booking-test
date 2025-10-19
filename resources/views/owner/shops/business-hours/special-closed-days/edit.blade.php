@extends('app')

@section('title', '特別休業日編集')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'specialClosedDay' => $special_closed_day,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
        ];
    @endphp
    <div id="app" data-page="owner-shops-business-hours-special-closed-days-edit" data-props="{{ json_encode($props) }}"></div>
@endsection
