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
        data-page="admin-contracts-create"
        data-props="{{ json_encode($props) }}"
    ></div>
@endsection