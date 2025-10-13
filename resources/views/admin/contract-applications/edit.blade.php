@extends('app')

@section('title', '契約申し込み編集')

@section('content')
    @php
        $props = [
            'contractApplication' => $contractApplication->load('user'),
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
        ];
    @endphp
    <div
        id="app"
        data-page="admin-contract-applications-edit"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
