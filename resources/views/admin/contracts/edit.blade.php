@extends('app')

@section('title', '契約編集')

@section('content')
    @php
        $props = [
            'contract' => $contract,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
        ];
    @endphp
    <div
        id="app"
        data-page="admin-contracts-edit"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection