@extends('app')

@section('title', '新規予約')

@section('content')
<div class="container">
    {{-- Vueコンポーネントに渡すデータをdata-*属性で定義 --}}
    <div
        id="app"
        data-page="booker-bookings-create"
        data-action="{{ route('booker.bookings.preview') }}"
        data-api-availability="{{ route('api.booker.bookings.availability') }}" {{-- 動的データ取得APIのURL --}}
    >
    </div>
</div>
@endsection
