@extends('app')

@section('title', 'オーナー一覧')

@section('content')
    @php
        // Vueコンポーネントに渡すデータを準備
        // 現状、一覧画面の初期表示に必要なpropsはないが、将来的な拡張のために残す
        $props = [];
    @endphp

    <div
        id="app"
        data-page="admin-owners-index"
        data-props="{{ json_encode($props) }}"
    >
    </div>
@endsection
