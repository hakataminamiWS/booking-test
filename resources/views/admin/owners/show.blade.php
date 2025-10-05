@extends('app')

@section('title', 'オーナー詳細')

@section('content')
    @php
        $props = ['owner' => $owner];
    @endphp
    <div
        id="app"
        data-page="admin-owners-show"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
