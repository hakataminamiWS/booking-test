@extends('app')

@section('title', '店舗トップ | ' . $shop->name)

@section('content')
    <div id="app" data-page="shop/Entry" data-props="{{ json_encode(['shop' => $shop]) }}"></div>
@endsection
