@extends('app')

@section('title', '予約サービスのお申し込み')

@section('content')
    @php
        $props['errors'] = $errors->all();
        $props['userId'] = $props['userId'] ?? null; // userId を追加
        $props['email'] = $props['email'] ?? null; // email を追加
    @endphp
    <div id="app" data-page="owner/contract/Apply" data-props="{{ json_encode($props) }}"></div>
@endsection
