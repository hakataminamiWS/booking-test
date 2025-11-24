@extends('app')

@section('title', '手動予約登録')

@section('content')
    @php
        // Vueコンポーネントに渡すpropsを準備
        $props = [
            'shop' => $shop,
            'menus' => $menus,
            'staffs' => $staffs,
            'bookers' => $bookers,
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
            'csrfToken' => csrf_token(),
        ];
    @endphp
    <div id="app" data-page="owner/shops/bookings/Create" data-props='{{ json_encode($props) }}'></div>
@endsection
