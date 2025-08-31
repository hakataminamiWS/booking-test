@extends('app')

@section('title', '契約管理')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="admin-contracts-index"
        data-contracts='@json($contracts)'
    >
    </div>
</div>
@endsection
