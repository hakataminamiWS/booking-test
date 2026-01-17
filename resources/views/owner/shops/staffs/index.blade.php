@extends('app')

@section('title', 'スタッフ一覧')

@section('content')
    @php
        $props = [
            'shop' => $shop,
            'csrfToken' => csrf_token(),
                    'flashSuccess' => session('success'),
            'flashError' => session('error'),
            'flashStatus' => session('status'),
        ];
    @endphp
    <div
        id="app"
        data-page="owner/shops/staffs/Index"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
