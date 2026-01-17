@extends('app')

@section('title', '店舗トップ | ' . $shop->name)

@section('content')
    <div id="app" data-page="shop/Entry" data-props="{{ json_encode(['shop' => $shop, 'flashSuccess' => session('success'), 'flashError' => session('error'), 'flashStatus' => session('status')]) }}"></div>
@endsection
