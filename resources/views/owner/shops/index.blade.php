@extends('app')

@section('title', '店舗一覧')

@section('content')
    @php
        $props = [
            'maxShops' => $maxShops,
            'currentShopsCount' => $currentShopsCount,
        ];
    @endphp
    <div
        id="app"
        data-page="owner-shops-index"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection