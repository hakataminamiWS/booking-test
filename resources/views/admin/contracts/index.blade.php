@extends('app')

@section('title', '契約一覧')

@section('content')
    @php
        $props = [            'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="admin/contracts/Index" data-props="{{ json_encode($props) }}"></div>
@endsection