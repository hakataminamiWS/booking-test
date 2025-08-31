@extends('app')

@section('title', 'スタッフ編集')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="owner-shops-staff-edit"
        data-shop-id="{{ $shop_id }}"
        data-staff-id="{{ $staff_id }}"
        data-staff-details='@json($staffDetails)'
    >
    </div>
</div>
@endsection
