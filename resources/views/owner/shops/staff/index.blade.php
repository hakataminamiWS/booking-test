@extends('app')

@section('title', 'スタッフ管理')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="owner-shops-staff-index"
        data-shop-id="{{ $shop_id }}"
        data-staffs='@json($staffs)'
    >
    </div>
</div>
@endsection
