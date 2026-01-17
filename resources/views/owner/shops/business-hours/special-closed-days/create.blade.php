@extends('app')

@section('title', '特別休業日登録')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="owner/shops/business-hours/special-closed-days/Create" data-props="{{ json_encode($props) }}"></div>
@endsection
