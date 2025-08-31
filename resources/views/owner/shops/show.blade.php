@extends('app')

@section('title', '店舗詳細')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="owner-shops-show"
        data-shop-id="{{ $shop_id }}"
        data-shop-details='@json($shopDetails)'
    >
    </div>
</div>
@endsection
