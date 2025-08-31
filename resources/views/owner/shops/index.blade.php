@extends('app')

@section('title', '店舗一覧')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="owner-shops-index"
        data-shops='@json($shops)'
    >
    </div>
</div>
@endsection
