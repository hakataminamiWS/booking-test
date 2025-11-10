@extends('app')

@section('title', '特別営業日編集')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'specialOpenDay' => $special_open_day,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/business-hours/special-open-days/Edit" data-props="{{ json_encode($props) }}"></div>
@endsection
