@extends('app')

@section('title', 'スタッフ登録申し込み完了')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="staff/applications/Complete" data-props='{{ json_encode($props) }}'></div>
@endsection
