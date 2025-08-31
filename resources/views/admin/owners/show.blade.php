@extends('app')

@section('title', 'オーナー詳細')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="admin-owners-show"
        data-owner-id="{{ $owner_id }}"
        data-owner-details='@json($ownerDetails)'
    >
    </div>
</div>
@endsection
