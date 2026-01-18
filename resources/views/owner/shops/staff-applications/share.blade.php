@extends('app')

@section('title', 'スタッフ申請リンク')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div
        id="app"
        data-page="owner/shops/staff-applications/Share"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
