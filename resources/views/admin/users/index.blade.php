@extends('app')

@section('title', 'オーナー権限一覧')

@section('content')
    @php
        $props = ['users' => $users];
    @endphp
    <div id="app" data-page="admin-users-index" data-props="{{ json_encode($props) }}"></div>
@endsection
