@extends('app')

@section('content')
    @if (app()->environment(['local', 'staging']))
        <div style="padding: 10px; background-color: #f0f0f0;">
            <h3>デバッグ用リンク</h3>
            <ul>
                <li><a href="{{ route('debug.login-as', ['user' => 5]) }}">予約者としてログイン (ID: 5)</a></li>
                <li><a href="{{ route('debug.login-as', ['user' => 6]) }}">予約者としてログイン (ID: 6)</a></li>
            </ul>
            <h3>管理者</h3>
            <ul>
                <li><a href="{{ route('debug.login-as', ['user' => 1]) }}">管理者としてログイン (ID: 1)</a></li>
                <li><a href="{{ route('admin.contract-applications.index') }}">契約申し込み一覧</a></li>
                <li><a href="{{ route('admin.contract-applications.show', ['contract_application' => 1]) }}">契約申し込み詳細
                        (ID: 1)</a></li>
                <li><a href="{{ route('admin.contract-applications.edit', ['contract_application' => 1]) }}">契約申し込み編集
                        (ID: 1)</a></li>
                <li><a href="{{ route('admin.contracts.create') }}">契約新規作成</a></li>
                <li><a href="{{ route('admin.contracts.index') }}">契約一覧</a></li>
                <li><a href="{{ route('admin.contracts.show', ['contract' => 1]) }}">契約詳細 (ID: 1)</a></li>
                <li><a href="{{ route('admin.contracts.edit', ['contract' => 1]) }}">契約編集 (ID: 1)</a></li>
            </ul>

            <h3>オーナー</h3>
            <ul>
                <li><a href="{{ route('debug.login-as', ['user' => 2]) }}">オーナーとしてログイン (ID: 2)</a></li>
                <li><a href="{{ route('contract.application.create') }}">オーナー契約申し込み画面</a></li>
                <li><a href="{{ route('owner.shops.index') }}">店舗一覧画面</a></li>
                <li><a href="{{ route('owner.shops.show', ['shop' => 'test-shop']) }}">店舗詳細画面 (Slug: test-shop)</a>
                </li>
                <li><a href="{{ route('owner.shops.business-hours.index', ['shop' => 'test-shop']) }}">営業時間一覧画面 (Slug:
                        test-shop)</a></li>
                <li><a href="{{ route('owner.shops.staff-applications.index', ['shop' => 'test-shop']) }}">スタッフ登録申し込み一覧画面
                        (Slug: test-shop)</a></li>
                <li><a href="{{ route('owner.shops.staffs.index', ['shop' => 'test-shop']) }}">スタッフ一覧画面 (Slug:
                        test-shop)</a></li>
                <li><a href="{{ route('owner.shops.shifts.index', ['shop' => 'test-shop']) }}">シフト一覧画面 (Slug:
                        test-shop)</a></li>
                <li><a href="{{ route('owner.shops.menus.index', ['shop' => 'test-shop']) }}">メニュー一覧画面 (Slug:
                        test-shop)</a></li>
                <li><a href="{{ route('owner.shops.options.index', ['shop' => 'test-shop']) }}">オプション一覧画面 (Slug:
                        test-shop)</a></li>
                <li><a href="{{ route('owner.shops.bookers.index', ['shop' => 'test-shop']) }}">予約者一覧画面 (Slug:
                        test-shop)</a></li>
                <li><a href="{{ route('owner.shops.bookings.index', ['shop' => 'test-shop']) }}">予約一覧画面 (Slug: test-shop)</a></li>

            </ul>

            <h3>スタッフ</h3>
            <ul>
                <li><a href="{{ route('debug.login-as', ['user' => 3]) }}">スタッフとしてログイン (テスト スタッフ1, ID: 3)</a></li>
                <li><a href="{{ route('debug.login-as', ['user' => 4]) }}">スタッフとしてログイン (テスト スタッフ2, ID: 4)</a></li>
                <li><a href="{{ route('staff.application.create', ['shop' => 'test-shop']) }}">スタッフ登録申し込み画面 (Slug:
                        test-shop)</a></li>
            </ul>
        </div>
    @endif
@endsection
