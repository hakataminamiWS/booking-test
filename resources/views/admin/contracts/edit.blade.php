@extends('app')

@section('title', '契約編集')

@section('content')
    @php
        $props = [
            'contract' => $contract,
            'csrfToken' => csrf_token(),
            'errors' => $errors->all(),
            'oldInput' => session()->getOldInput(),
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div
        id="app"
        data-page="admin/contracts/Edit"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection