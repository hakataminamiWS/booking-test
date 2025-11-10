@extends('app')

@section('title', '営業時間・定休日設定')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'businessHours' => $businessHours,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/business-hours/regular/Edit" data-props="{{ json_encode($props) }}"></div>
@endsection
