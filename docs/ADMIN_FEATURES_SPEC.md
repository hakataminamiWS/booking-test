# 管理者機能仕様書

## 1. 目的

このドキュメントは、システム管理者 (Admin) が利用する機能のUI/UX、APIエンドポイント、およびデータインタラクションに関する詳細な仕様を定義します。

## 2. 機能一覧

### 2.1. オーナー管理
オーナー（店舗の所有者）アカウントのライフサイクルを管理します。

-   **ユーザー一覧表示**: システムに登録されている全ユーザーの一覧を確認する。
-   **ユーザーのオーナーへの権限付与**: 一般ユーザーに対して、「オーナー」の役割を付与する。

### 2.2. 契約管理
オーナーがサービスを利用するための契約を作成・管理します。

-   **契約の新規作成**: 「どのオーナー」が「何店舗まで」店舗を作成できるか、といった契約内容を登録する。
-   **契約の更新**: 契約期間の延長や、ステータスの変更、店舗上限数の変更を行う。
-   **契約の削除**: 契約を無効化する。

## 3. 対象ユーザー

-   Admin (システム管理者)

---

## 4. 契約管理機能

### 4.1. 機能概要

管理者は、特定のオーナー (User) に対して「契約 (Contract)」を作成・管理します。契約は、オーナーが自身の店舗を作成し、管理するための権限の根拠となります。

### 4.2. 画面仕様詳細

#### ユーザー詳細画面 (`/admin/users/{user}`)

契約の追加は、ユーザー詳細画面から行います。

-   **URL**: `/admin/users/{user}`
-   **コントローラ**: `App\Http\Controllers\Admin\UsersController@show`

##### UI要素

1.  **ユーザー情報**:
    -   画面上部に、対象ユーザーのIDなどを表示します。

2.  **現在の契約情報**:
    -   このユーザーが既に契約を持っている場合、その内容を表示します。
    -   表示項目: 契約ステータス, 契約期間, 店舗上限数
    -   「契約編集」ボタンを配置します（※将来実装）。

3.  **新規契約追加フォーム**:
    -   **目的**: このユーザーに対して新しい契約を作成します。
    -   **フォーム送信先**: `POST /admin/contracts` (ルート名: `admin.contracts.store`)
    -   **フォーム項目**:
        -   **店舗上限数**:
            -   UI: `<input type="number">`
            -   デフォルト値: 1
            -   必須項目。
        -   **契約開始日**:
            -   UI: `<input type="date">`
            -   必須項目。
        -   **契約終了日**:
            -   UI: `<input type="date">`
            -   必須項目。
        -   **契約ステータス**:
            -   UI: `<select>` プルダウン
            -   選択肢: `active` (有効), `pending` (保留) など。
            -   必須項目。
        -   **ユーザーID (Hidden)**:
            -   UI: `<input type="hidden" name="user_id">`
            -   値: 現在表示しているユーザーのID。
    -   **ボタン**: 「契約を作成する」

### 4.3. バックエンド仕様

#### ルート定義 (`routes/web.php`)

```php
Route::prefix('admin')->name('admin.')->group(function () {
    // ...
    Route::resource('users', App\Http\Controllers\Admin\UsersController::class)->only(['index', 'show']);
    Route::resource('contracts', App\Http\Controllers\Admin\ContractsController::class)->only(['store']);
});
```

#### コントローラ (`Admin\ContractsController`)

-   **`store` メソッド**:
    -   `StoreContractRequest` を使ってバリデーションと認可を行います。
    -   `contracts` テーブルに新しいレコードを作成します。
    -   成功後、元のユーザー詳細画面 (`/admin/users/{user}`) にリダイレクトし、成功メッセージを表示します。

#### バリデーション (`App\Http\Requests\Admin\StoreContractRequest`)

-   `authorize` メソッド:
    -   リクエストを送信したユーザーが管理者 (`isAdmin()`) であることを確認します。
-   `rules` メソッド:
    -   `user_id`: 必須、`users`テーブルに存在すること。
    -   `max_shops`: 必須、数値であり、1以上であること。
    -   `start_date`: 必須、日付形式であること。
    -   `end_date`: 必須、日付形式であり、`start_date`以降であること。
    -   `status`: 必須、指定された値（例: `active`, `pending`）のいずれかであること。
