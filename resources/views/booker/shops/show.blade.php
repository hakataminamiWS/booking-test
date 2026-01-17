@extends('app')

@section('title', '店舗マイページ | ' . $shop->name)

@section('content')
    <div id="app" data-page="booker/shops/Show" data-props="{{ json_encode(['shop' => $shop, 'booker' => $booker, 'flashSuccess' => session('success'), 'flashError' => session('error'), 'flashStatus' => session('status')]) }}"></div>
@endsection
