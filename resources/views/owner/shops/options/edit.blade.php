@extends('app')

@section('title', 'オプション編集')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'option' => $option,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/options/Edit" data-props="{{ json_encode($props) }}">
    </div>
@endsection
