@extends('app')

@section('title', '新規予約')

@section('content')
<div class="container">
    {{-- Vueコンポーネントに渡すデータをdata-*属性で定義 --}}
    <div
        id="app"
        data-page="booker-bookings-create"
        data-action="{{ route('booker.bookings.store', ['shop_id' => $shop_id]) }}"
        data-api-availability="{{ route('api.booker.availability', ['shop_id' => $shop_id]) }}" {{-- 動的データ取得APIのURL --}}
    >
    </div>
</div>
@endsection
