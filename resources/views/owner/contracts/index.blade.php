@extends('app')

@section('title', '契約管理')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="owner-contracts-index"
        data-contracts='@json($contracts)'
    >
    </div>
</div>
@endsection
