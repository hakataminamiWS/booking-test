@extends('app')

@section('content')
    @if (app()->environment(['local', 'staging']))
        <div style="padding: 10px; background-color: #f0f0f0;">
            <h3>デバッグ用リンク</h3>
            <ul>
                <li><a href="{{ route('debug.login-as', ['user' => 4]) }}">予約者としてログイン (ID: 4)</a></li>
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
                <li><a href="{{ route('owner.shops.create') }}">店舗登録画面</a></li>
                <li><a href="{{ route('owner.shops.show', ['shop' => 'test-shop']) }}">店舗詳細画面 (Slug: test-shop)</a>
                </li>
                <li><a href="{{ route('owner.shops.edit', ['shop' => 'test-shop']) }}">店舗詳細管理画面 (Slug: test-shop)</a>
                </li>
                <li><a href="{{ route('owner.shops.business-hours.index', ['shop' => 'test-shop']) }}">営業時間一覧画面 (Slug:
                        test-shop)</a></li>
                <li><a href="{{ route('owner.shops.business-hours.regular.edit', ['shop' => 'test-shop']) }}">営業時間・定休日編集画面
                        (Slug: test-shop)</a></li>
                <li><a href="{{ route('owner.shops.business-hours.special-open-days.create', ['shop' => 'test-shop']) }}">特別営業日登録画面
                        (Slug: test-shop)</a></li>
                <li><a
                        href="{{ route('owner.shops.business-hours.special-open-days.edit', ['shop' => 'test-shop', 'special_open_day' => 1]) }}">特別営業日編集画面
                        (Slug: test-shop, ID: 1)</a></li>
                <li><a
                        href="{{ route('owner.shops.business-hours.special-closed-days.create', ['shop' => 'test-shop']) }}">特別休業日登録画面
                        (Slug: test-shop)</a></li>
                <li><a
                        href="{{ route('owner.shops.business-hours.special-closed-days.edit', ['shop' => 'test-shop', 'special_closed_day' => 1]) }}">特別休業日編集画面
                        (Slug: test-shop, ID: 1)</a></li>
                <li><a href="{{ route('owner.shops.staff-applications.index', ['shop' => 'test-shop']) }}">スタッフ登録申し込み一覧画面
                        (Slug: test-shop)</a></li>
                <li><a href="{{ route('owner.shops.staffs.index', ['shop' => 'test-shop']) }}">スタッフ一覧画面 (Slug: test-shop)</a></li>
                <li><a href="{{ route('owner.shops.staffs.edit', ['shop' => 'test-shop', 'staff' => 1]) }}">スタッフプロフィール編集画面 (Slug: test-shop, ID: 1)</a></li>
            </ul>

            <h3>スタッフ</h3>
            <ul>
                <li><a href="{{ route('debug.login-as', ['user' => 3]) }}">スタッフとしてログイン (ID: 3)</a></li>
                <li><a href="{{ route('staff.application.create', ['shop' => 'test-shop']) }}">スタッフ登録申し込み画面 (Slug:
                        test-shop)</a></li>
            </ul>
        </div>
    @endif
@endsection
