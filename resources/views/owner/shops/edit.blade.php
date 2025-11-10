@extends('app')

@section('title', '店舗情報編集: ' . $shop->name)

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
        ];
    @endphp
    <div
        id="app"
        data-page="owner/shops/Edit"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
