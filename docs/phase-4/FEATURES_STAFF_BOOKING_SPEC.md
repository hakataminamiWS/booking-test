# 店舗スタッフ向け予約管理機能仕様書

## 1. 目的

このドキュメントは、店舗スタッフ (Staff) が利用する予約管理機能の UI/UX、API エンドポイント、
およびデータインタラクションに関する詳細な仕様を定義します。
基本的には店舗オーナー向け機能 (`FEATURES_OWNER_BOOKING_SPEC.md`) とほぼ同様ですが、
エンドポイントおよび認可ポリシーがスタッフ用に分離されています。

## 2. 対象ユーザー

-   Staff (店舗スタッフ)

---

## 3. 機能・画面仕様詳細

### 予約一覧画面

#### 機能概要

スタッフが自店舗の予約を一覧で確認し、検索や絞り込みを行うための基本画面です。
ここから各予約の詳細確認や、手動での新規予約登録へ進みます。
一覧画面は、`docs/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された標準パターンで実装します。

#### 画面仕様詳細 (`/shops/{shop}/staff/bookings`)

##### 表示項目一覧

店舗ヘッダー：

-   `店舗：{店舗名}` の共通ヘッダーを表示します。

予約一覧セクション：

-   セクションタイトル: `予約一覧`
-   「手動で予約を登録する」ボタン:
    -   セクションタイトルと横並びで配置します（ボタンは右寄せ）。
    -   クリックすると手動予約登録画面 (`/shops/{shop}/staff/bookings/create`) へ遷移します。

| カラム（ラベル） | 表示内容 | フィルタ | ソート | 操作 |
| :--- | :--- | :--- | :--- | :--- |
| 予約日時 | `bookings.start_at` | 可 (日付範囲) | 可 | |
| 予約者番号 | `shop_bookers.number` | 可 (数値入力) | 可 | |
| 顧客名 | `bookings.booker_name` | 可 (テキスト入力) | 可 | |
| メニュー | `bookings.menu_name` | 可 (セレクト) | 不可 | |
| 担当スタッフ | `bookings.assigned_staff_name` | 可 (セレクト) | 不可 | |
| ステータス | `bookings.status` | 可 (セレクト) | 可 | |
| 合計料金 | `bookings.menu_price` + options | 可 (数値範囲) | 可 | |
| 予約経路 | `bookings.booking_channel` | 可 (セレクト) | 可 | |
| 操作 | 「編集」ボタン | 不可 | なし | 編集画面へ遷移 |

#### バックエンド仕様

##### ページ表示時のデータ

`Staff\BookingController@index` は、Vue コンポーネントをマウントする Blade ビューを返します。

| データ名 | 型 | 説明 |
| :--- | :--- | :--- |
| `shop` | `object` | 現在の店舗情報。 |
| `menus` | `array` | フィルタ用のメニュー一覧。 |
| `staffs` | `array` | フィルタ用のスタッフ一覧。 |

##### API エンドポイント

-   **URL**: `GET /api/shops/{shop}/staff/bookings`
    - (将来的に `routes/staff.php` などで定義、プレフィックスは要調整。`api/staff/shops/{shop}/bookings` の可能性もあり)
-   **コントローラ**: `Api\Staff\BookingController@index`
-   **リクエストクラス**: `App\Http\Requests\Api\Staff\IndexBookingsRequest`
-   **クエリパラメータ**: オーナー機能と同様。

---

### 手動予約登録画面

#### 機能概要

スタッフが、電話や対面などで受け付けた予約を管理画面から直接登録するための画面です。

#### 画面仕様詳細 (`/shops/{shop}/staff/bookings/create`)

-   **ウィンドウタイトル**: `手動予約登録`
-   **画面構成**: オーナー機能と同様。

##### バックエンド仕様

-   **ルート**: `GET /shops/{shop}/staff/bookings/create`
-   **コントローラ**: `Staff\BookingController@create`
-   **処理内容**:
    -   `BookingPolicy@create` (または `StaffBookingPolicy`) で認可チェック。
    -   フォームに必要なマスタデータ (`menus`, `staffs`, `options`) を取得し `props` へ。

###### 登録処理 (`store`)

-   **ルート**: `POST /shops/{shop}/staff/bookings`
-   **コントローラ**: `Staff\BookingController@store`
-   **リクエストクラス**: `StoreBookingRequest` (オーナーと共用、またはスタッフ用 `StoreStaffBookingRequest` を作成して継承)
    -   必須項目: `start_at`, `menu_id`, `booker_name`, `contact_email`, `contact_phone` 等。
    -   バリデーションルールはオーナー機能と同一（連絡先必須）。

---

### 予約編集画面

#### 機能概要

スタッフが既存の予約内容を変更、または予約自体を削除（キャンセル）するための画面です。

#### 画面仕様詳細 (`/shops/{shop}/staff/bookings/{booking}/edit`)

-   **ウィンドウタイトル**: `予約編集`
-   **画面構成**: オーナー機能と同様。

##### バックエンド仕様

-   **ルート**: `GET /shops/{shop}/staff/bookings/{booking}/edit`
-   **コントローラ**: `Staff\BookingController@edit`

###### 更新処理 (`update`)

-   **ルート**: `PUT /shops/{shop}/staff/bookings/{booking}`
-   **コントローラ**: `Staff\BookingController@update`

###### 削除処理 (`destroy`)

-   **ルート**: `DELETE /shops/{shop}/staff/bookings/{booking}`
-   **コントローラ**: `Staff\BookingController@destroy`
