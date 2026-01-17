@extends('app')

@section('title', 'オプション一覧')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'errors' => $errors->all(),
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div id="app" data-page="owner/shops/options/Index" data-props="{{ json_encode($props) }}">
    </div>
@endsection
