@extends('app')

@section('content')
    @if (app()->environment(['local', 'staging']))
        <div style="padding: 10px; background-color: #f0f0f0;">
            <h3>管理者</h3>
            <ul>
                <li><a href="{{ route('debug.login-as', ['user' => 1]) }}">管理者としてログイン (ID: 1)</a></li>
                <li><a href="{{ route('admin.contract-applications.index') }}">契約申し込み一覧</a></li>
                <li><a href="{{ route('admin.contracts.index') }}">契約一覧</a></li>
            </ul>

            <h3>オーナー</h3>
            <ul>
                <li><a href="{{ route('debug.login-as', ['user' => 2]) }}">オーナーとしてログイン (ID: 2)</a></li>
                <li><a href="{{ route('contract.application.create') }}">オーナー契約申し込み画面</a></li>
                <li><a href="{{ route('owner.shops.index') }}">店舗一覧画面</a></li>
                <li><a href="{{ route('owner.shops.dashboard', ['shop' => 'test-shop']) }}">店舗ダッシュボード (Slug: test-shop)</a></li>
            </ul>

            <h3>スタッフ</h3>
            <ul>
                <li><a href="{{ route('debug.login-as', ['user' => 3]) }}">スタッフとしてログイン (テスト スタッフ1, ID: 3)</a></li>
                <li><a href="{{ route('debug.login-as', ['user' => 4]) }}">スタッフとしてログイン (テスト スタッフ2, ID: 4)</a></li>
                <li><a href="{{ route('staff.application.create', ['shop' => 'test-shop']) }}">スタッフ登録申し込み画面 (Slug:
                        test-shop)</a></li>
                <li><a href="{{ route('staff.dashboard', ['shop' => 'test-shop']) }}">スタッフダッシュボード (Slug: test-shop)</a></li>
            </ul>

            <h3>会員 (Booker)</h3>
            <ul>
                <li><a href="{{ route('debug.login-as', ['user' => 5]) }}">店舗予約者としてログイン (ID: 5)</a></li>
                <li><a href="{{ route('debug.login-as-unregistered', ['shop' => 'test-shop']) }}">店舗予約者 (ID: 6)を店舗未登録にしてログイン</a></li>
                <li><a href="{{ route('booker.shop.show', ['shop' => 'test-shop']) }}">店舗マイページ (登録店舗詳細)</a></li>
            </ul>

            <h3>予約フロー</h3>
            <ul>
                <li><a href="{{ route('shop.entry', ['shop' => 'test-shop']) }}">店舗予約トップ (ゲスト/ログイン分岐)</a></li>
            </ul>
        </div>
    @endif
@endsection
