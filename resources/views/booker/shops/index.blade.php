@extends('app')

@section('title', '登録店舗一覧')

@section('content')
    <div
        id="app"
        data-page="booker/shops/Index"
        data-props="{{ json_encode(['flashSuccess' => session('success'), 'flashError' => session('error'), 'flashStatus' => session('status')]) }}"
    >
    </div>
@endsection
