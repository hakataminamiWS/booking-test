@extends('app')

@section('title', 'オーナー情報編集')

@section('content')
    @php
        $props = [
            'owner' => $owner,
            'errors' => session()->get('errors') ? session()->get('errors')->getBag('default')->getMessages() : [],
        ];
    @endphp
    <div
        id="app"
        data-page="admin-owners-edit"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
