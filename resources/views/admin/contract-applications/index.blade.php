@extends('app')

@section('title', '契約申し込み一覧')

@section('content')
    <div id="app" data-page="admin/contract-applications/Index" data-props="{{ json_encode(['flashSuccess' => session('success'), 'flashError' => session('error'), 'flashStatus' => session('status')]) }}"></div>
@endsection
