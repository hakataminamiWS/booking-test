@extends('app')

@section('title', '契約新規作成')

@section('content')
    @php
        $props = [
            'application' => $application,
            'errors' => $errors->getMessages(),
        ];
    @endphp
    <div
        id="app"
        data-page="admin/contracts/Create"
        data-props="{{ json_encode($props) }}"
    ></div>
@endsection