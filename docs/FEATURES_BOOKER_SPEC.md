# 会員機能仕様書

## 1. 目的

このドキュメントは、会員登録した予約者 (Booker) が自身の情報や予約を管理するための機能の UI/UX、API エンドポイント、およびデータインタラクションに関する詳細な仕様を定義します。

## 2. 対象ユーザー

-   会員 (Booker): 各店舗に予約者として登録済みで、かつユーザーアカウントと紐付いた予約者

---

## 3. 主な画面と機能

本仕様書で定義する主な画面と、その機能概要は以下の通りです。

### 店舗管理

-   **登録店舗一覧画面 (`/booker/shops`)**: 会員が予約者として登録されている店舗を一覧で確認し、各店舗へアクセスします。

### プロフィール管理

-   **プロフィール編集画面 (`/shops/{shop}/booker/profile/edit`)**: 会員が自身のプロフィール情報（名前、連絡先など）を編集します。

### 予約管理

-   **予約一覧画面 (`/shops/{shop}/booker/bookings`)**: 会員が自身の予約履歴を一覧で確認します。
-   **予約登録画面 (`/shops/{shop}/booker/bookings/create`)**: 会員が新しい予約を登録します。
-   **予約詳細画面 (`/shops/{shop}/booker/bookings/{booking}`)**: 予約の詳細を確認し、キャンセル操作を行います。

---

## 4. 機能・画面仕様詳細

### 登録店舗一覧画面

#### 機能概要

会員が予約者として登録されている店舗を一覧で確認し、各店舗のプロフィール編集や予約管理へアクセスするためのハブ画面です。
オーナー向け店舗一覧画面 (`FEATURES_OWNER_SPEC.md`) を参考に、会員向けにアレンジします。
一覧画面は、`docs/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された標準パターンで実装します。

#### 画面仕様詳細 (`/booker/shops`)

##### 表示項目一覧

-   ウィンドウタイトル: `登録店舗一覧`
-   ページタイトル: `登録店舗一覧`

| カラム（ラベル） | 表示内容                           | フィルタ          | ソート | 操作                     |
| :--------------- | :--------------------------------- | :---------------- | :----- | :----------------------- |
| **店舗名**       | `shops.name`                       | 可 (テキスト入力) | 可     |                          |
| **最終予約日**   | `shop_bookers_crm.last_booking_at` | 可 (日付範囲)     | 可     |                          |
| **予約回数**     | `shop_bookers_crm.booking_count`   | 不可              | 可     |                          |
| **操作**         | 「詳細」ボタン                     | 不可              | なし   | 店舗詳細画面へ遷移       |

##### 操作リンク（詳細画面から）

店舗詳細画面、または一覧の各行から以下へ遷移できます:

| リンク         | 遷移先                                       |
| :------------- | :------------------------------------------- |
| プロフィール   | `/shops/{shop}/booker/profile/edit`          |
| 予約履歴       | `/shops/{shop}/booker/bookings`              |
| 新しい予約     | `/shops/{shop}/booker/bookings/create`       |

#### バックエンド仕様

##### ページ表示時のデータ

`Booker\ShopsController@index` は、Vue コンポーネントをマウントする Blade ビューを返します。

| データ名 | 型       | 説明               |
| :------- | :------- | :----------------- |
| なし     | -        | API で取得。       |

##### API エンドポイント

-   **URL**: `GET /api/booker/shops`
-   **コントローラ**: `Api\Booker\ShopsController@index`
-   **リクエストクラス**: `App\Http\Requests\Api\Booker\IndexShopsRequest`
-   **クエリパラメータ**:
    -   `name` (string): 店舗名による部分一致検索。
    -   `last_booking_at_from` (date): 最終予約日の開始範囲。
    -   `last_booking_at_to` (date): 最終予約日の終了範囲。
    -   `sort_by` (string): ソート対象カラム。許可される値は `name`, `last_booking_at`, `booking_count`。
    -   `sort_order` (string): ソート方向 (`asc` or `desc`)。
    -   `page` (integer): ページ番号。
    -   `per_page` (integer): 1 ページあたりの表示件数。

##### 処理内容

-   ログインしているユーザーに紐づく `shop_bookers` テーブルと `shops` テーブルを結合して取得します。
-   `shop_bookers_crm` テーブルも結合し、最終予約日と予約回数を取得します。
-   受け取ったクエリパラメータに基づき、動的なフィルタリング、ソーティングを適用します。
-   ページネーションを適用して結果を返却します。

---

### プロフィール編集画面

#### 機能概要

会員が自身の予約者情報（名前、連絡先など）を編集するための画面です。
店舗スタッフ向けのプロフィール編集画面 (`FEATURES_STAFF_SPEC.md`) を参考に、会員向けにアレンジします。

#### 画面仕様詳細 (`/shops/{shop}/booker/profile/edit`)

##### 表示項目一覧

店舗ヘッダー：

-   `店舗：{店舗名}` の共通ヘッダーを表示します。

プロフィール編集セクション：

-   **セクションタイトル**: `プロフィール編集`
-   フォーム全体は `<v-card>` で囲みます。
-   カードの最下部に「更新する」ボタンを配置します。

##### フォーム項目一覧

| ラベル               | UI           | `name`属性      | 備考                     |
| :------------------- | :----------- | :-------------- | :----------------------- |
| **お名前**           | テキスト入力 | `name`          | 必須。                   |
| **よみがな**         | テキスト入力 | `name_kana`     | 任意。                   |
| **連絡先メールアドレス** | テキスト入力 | `contact_email` | 必須（メールまたは電話）。 |
| **連絡先電話番号**   | テキスト入力 | `contact_phone` | 必須（メールまたは電話）。 |

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /shops/{shop:slug}/booker/profile/edit`
-   **コントローラ**: `App\Http\Controllers\Booker\ProfileController@edit`
-   **処理内容**:
    -   `ShopBookerPolicy@update` を使用し、会員が自身のプロフィール情報のみを編集できるか認可チェックを行います。
    -   認可に失敗した場合は、403 エラーページを表示します。
    -   成功した場合は、対象の `ShopBooker` オブジェクトを `booker.profile.edit` ビューに渡します。
-   CSRF トークンを Blade から Vue へ渡します。（`docs/ARCHITECTURE.md` の方式に従う）

##### フォーム送信 (更新処理)

-   **ルート**: `PUT /shops/{shop:slug}/booker/profile`
-   **コントローラ**: `App\Http\Controllers\Booker\ProfileController@update`
-   **リクエストクラス**: `App\Http\Requests\Booker\UpdateProfileRequest`

##### バリデーション

-   **フロントエンド**:
    -   フォーム送信前に、必須項目が入力されているかチェックします。
-   **バックエンド (`UpdateProfileRequest`)**:
    -   **認可**: `authorize`メソッド内で`ShopBookerPolicy@update`を呼び出します。
    -   **ルール**:
        -   `name`: `required`, `string`, `max:255`
        -   `name_kana`: `nullable`, `string`, `max:255`
        -   `contact_email`: `required_without:contact_phone`, `nullable`, `email`, `max:255`
        -   `contact_phone`: `required_without:contact_email`, `nullable`, `string`, `max:20`

##### 処理内容

1.  `UpdateProfileRequest` で認可とバリデーションを実行します。
2.  バリデーションが成功した場合、対象の `ShopBooker` モデルおよび `ShopBookersCrm` モデルの情報を更新します。
3.  更新後、プロフィール編集画面にリダイレクトし、「プロフィールを更新しました」という成功メッセージを表示します。

##### API エンドポイント

-   この画面はサーバーサイドで完結するため、データ取得や更新のための API エンドポイントは提供しません。

---

### 予約一覧画面

#### 機能概要

会員が自身の予約履歴を一覧で確認するための画面です。
店舗スタッフ向け予約一覧画面 (`FEATURES_STAFF_BOOKING_SPEC.md`) を参考に、会員向けにアレンジします。
一覧画面は、`docs/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された標準パターンで実装します。

#### 画面仕様詳細 (`/shops/{shop}/booker/bookings`)

##### 表示項目一覧

店舗ヘッダー：

-   `店舗：{店舗名}` の共通ヘッダーを表示します。

予約一覧セクション：

-   セクションタイトル: `予約履歴`
-   **「新しい予約をする」ボタン**:
    -   セクションタイトルと横並びで配置します（ボタンは右寄せ）。
    -   クリックすると予約登録画面 (`/shops/{shop}/booker/bookings/create`) へ遷移します。

| カラム（ラベル） | 表示内容                           | フィルタ          | ソート | 操作             |
| :--------------- | :--------------------------------- | :---------------- | :----- | :--------------- |
| **予約日時**     | `bookings.start_at`                | 可 (日付範囲)     | 可     |                  |
| **メニュー**     | `bookings.menu_name`               | 可 (セレクト)     | 不可   |                  |
| **担当スタッフ** | `bookings.assigned_staff_name`     | 不可              | 不可   |                  |
| **ステータス**   | `bookings.status`                  | 可 (セレクト)     | 可     |                  |
| **合計料金**     | `bookings.menu_price` + options    | 不可              | 不可   |                  |
| **操作**         | 「詳細」ボタン                     | 不可              | なし   | 詳細画面へ遷移   |

#### バックエンド仕様

##### ページ表示時のデータ

`Booker\BookingController@index` は、Vue コンポーネントをマウントする Blade ビューを返します。

| データ名 | 型       | 説明               |
| :------- | :------- | :----------------- |
| `shop`   | `object` | 現在の店舗情報。   |
| `menus`  | `array`  | フィルタ用のメニュー一覧。 |

##### API エンドポイント

-   **URL**: `GET /api/shops/{shop}/booker/bookings`
-   **コントローラ**: `Api\Booker\BookingController@index`
-   **リクエストクラス**: `App\Http\Requests\Api\Booker\IndexBookingsRequest`
-   **クエリパラメータ**:
    -   `start_at_from` (date): 予約日時の開始範囲。
    -   `start_at_to` (date): 予約日時の終了範囲。
    -   `menu_id` (integer): メニュー ID によるフィルタ。
    -   `status` (string): ステータスによるフィルタ。
    -   `sort_by` (string): ソート対象カラム。許可される値は `start_at`, `status`。
    -   `sort_order` (string): ソート方向 (`asc` or `desc`)。
    -   `page` (integer): ページ番号。
    -   `per_page` (integer): 1 ページあたりの表示件数。

##### 処理内容

-   ログインしている会員に紐づく `bookings` テーブルのレコードを主軸とします。
-   `BookingPolicy@viewAny` により、認可されたデータ（自身の予約のみ）が返却されることを保証します。
-   受け取ったクエリパラメータに基づき、動的なフィルタリング、ソーティングを適用します。
-   ページネーションを適用して結果を返却します。

---

### 予約登録画面

#### 機能概要

会員が新しい予約を登録するための画面です。
店舗スタッフ向け手動予約登録画面 (`FEATURES_STAFF_BOOKING_SPEC.md`) を参考に、会員向けにアレンジします。

#### 画面仕様詳細 (`/shops/{shop}/booker/bookings/create`)

-   **ウィンドウタイトル**: `新規予約`

##### 表示項目一覧

店舗ヘッダー：

-   `店舗：{店舗名}` の共通ヘッダーを表示します。

予約登録セクション：

-   **セクションタイトル**: `新規予約`
-   フォーム全体は `<v-card>` で囲みます。
-   カードの最下部に「予約する」ボタンを配置します。

##### フォーム項目一覧

| ラベル             | UI               | `name`属性       | 備考                                 |
| :----------------- | :--------------- | :--------------- | :----------------------------------- |
| **予約日時**       | 日時入力         | `start_at`       | 必須。空き時間選択 UI を使用。       |
| **メニュー**       | セレクトボックス | `menu_id`        | 必須。                               |
| **オプション**     | チェックボックス | `option_ids[]`   | 任意。複数選択可。                   |
| **担当スタッフ**   | セレクトボックス | `staff_id`       | 任意。指名予約の場合。               |
| **性別希望**       | ラジオボタン     | `preferred_gender` | 任意。                               |
| **備考**           | テキストエリア   | `notes`          | 任意。                               |

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /shops/{shop:slug}/booker/bookings/create`
-   **コントローラ**: `App\Http\Controllers\Booker\BookingController@create`
-   **処理内容**:
    -   `BookingPolicy@create` を使用し、会員が予約を作成できるか認可チェックを行います。
    -   フォームに必要なマスタデータ (`menus`, `staffs`, `options`) を取得し `props` へ渡します。

##### フォーム送信 (登録処理)

-   **ルート**: `POST /shops/{shop:slug}/booker/bookings`
-   **コントローラ**: `App\Http\Controllers\Booker\BookingController@store`
-   **リクエストクラス**: `App\Http\Requests\Booker\StoreBookingRequest`

##### バリデーション

-   **フロントエンド**:
    -   フォーム送信前に、必須項目が入力されているかチェックします。
-   **バックエンド (`StoreBookingRequest`)**:
    -   **認可**: `authorize`メソッド内で`BookingPolicy@create`を呼び出します。
    -   **ルール**:
        -   `start_at`: `required`, `date`, `after:now`
        -   `menu_id`: `required`, `exists:shop_menus,id`
        -   `option_ids`: `nullable`, `array`
        -   `option_ids.*`: `exists:shop_options,id`
        -   `staff_id`: `nullable`, `exists:shop_staffs,id`
        -   `preferred_gender`: `nullable`, `in:male,female,any`
        -   `notes`: `nullable`, `string`, `max:1000`

##### 処理内容

1.  `StoreBookingRequest` で認可とバリデーションを実行します。
2.  バリデーションが成功した場合、`bookings` テーブルに新しいレコードを作成します。
    -   予約者情報 (`booker_name`, `contact_email`, `contact_phone`) は、ログイン中の会員の `ShopBooker` から取得して自動設定します。
    -   `booking_channel` は `online` (会員予約) として設定します。
3.  登録後、予約一覧画面 (`/shops/{shop}/booker/bookings`) にリダイレクトし、「予約が完了しました」という成功メッセージを表示します。

##### API エンドポイント（空き時間取得用）

-   **URL**: `GET /api/shops/{shop}/booker/available-slots`
-   **コントローラ**: `Api\Booker\AvailableSlotController@index`
-   **クエリパラメータ**:
    -   `date` (date): 対象日付
    -   `menu_id` (integer): メニュー ID
    -   `staff_id` (integer): スタッフ ID（任意）
-   **レスポンス**:
    -   利用可能な時間枠の配列 `[{ "start_at": "2024-01-15T10:00:00", "end_at": "2024-01-15T10:30:00" }, ...]`

---

### 予約詳細・キャンセル機能

#### 機能概要

会員が自身の予約の詳細を確認し、必要に応じてキャンセルを行うための画面です。

#### 画面仕様詳細 (`/shops/{shop}/booker/bookings/{booking}`)

-   **ウィンドウタイトル**: `予約詳細`

##### 表示項目一覧

店舗ヘッダー：

-   `店舗：{店舗名}` の共通ヘッダーを表示します。

予約詳細セクション：

-   **セクションタイトル**: `予約詳細`
-   予約情報は `<v-card>` で囲み、読み取り専用で表示します。

| 項目             | データソース                       | 表示形式                              |
| :--------------- | :--------------------------------- | :------------------------------------ |
| 予約日時         | `bookings.start_at`                | 日時（例: 2024年1月15日 10:00）       |
| メニュー         | `bookings.menu_name`               | テキスト                              |
| オプション       | `booking_options`                  | テキスト（カンマ区切り）              |
| 担当スタッフ     | `bookings.assigned_staff_name`     | テキスト                              |
| 合計料金         | `bookings.menu_price` + options    | 金額（例: ¥5,500）                    |
| ステータス       | `bookings.status`                  | テキスト（例: 確定済み / キャンセル済み）|
| 備考             | `bookings.notes`                   | テキスト                              |

キャンセルセクション：

-   **キャンセルボタン**:
    -   予約がキャンセル可能な状態（ステータスが `pending` または `confirmed`）かつ、キャンセル期限内の場合に表示します。
    -   ボタンクリックで確認ダイアログを表示し、確認後にキャンセル処理を実行します。
-   **キャンセル不可メッセージ**:
    -   キャンセル期限を過ぎている場合、「キャンセル期限を過ぎているため、キャンセルできません。店舗に直接お問い合わせください。」と表示します。

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /shops/{shop:slug}/booker/bookings/{booking}`
-   **コントローラ**: `App\Http\Controllers\Booker\BookingController@show`
-   **処理内容**:
    -   `BookingPolicy@view` を使用し、会員が自身の予約のみを閲覧できるよう認可チェックを行います。
    -   対象の `Booking` オブジェクトを `booker.bookings.show` ビューに渡します。
    -   キャンセル可否の判定結果も `props` として渡します。

##### キャンセル処理

-   **ルート**: `DELETE /shops/{shop:slug}/booker/bookings/{booking}`
-   **コントローラ**: `App\Http\Controllers\Booker\BookingController@destroy`

##### バリデーション・認可

-   **認可**: `BookingPolicy@delete` を使用し、以下をチェックします:
    -   予約が自身のものであること
    -   予約ステータスが `pending` または `confirmed` であること
    -   キャンセル期限内であること（`shops.cancellation_deadline_minutes` に基づく）

##### 処理内容

1.  認可チェックを実行します。
2.  認可が成功した場合、予約のステータスを `cancelled` に更新します。
3.  キャンセル完了後、予約一覧画面 (`/shops/{shop}/booker/bookings`) にリダイレクトし、「予約をキャンセルしました」という成功メッセージを表示します。

##### API エンドポイント

-   この画面はサーバーサイドで完結するため、データ取得や更新のための API エンドポイントは提供しません。

---

## 5. フォルダ・ファイル構成

### Controller

```
app/Http/Controllers/Booker/
├── ShopsController.php         # 登録店舗一覧
├── ProfileController.php       # プロフィール管理
└── BookingController.php       # 予約管理

app/Http/Controllers/Api/Booker/
├── ShopsController.php         # 登録店舗一覧 API
├── BookingController.php       # 予約一覧 API
└── AvailableSlotController.php # 空き時間取得 API
```

### Request

```
app/Http/Requests/Booker/
├── UpdateProfileRequest.php
└── StoreBookingRequest.php

app/Http/Requests/Api/Booker/
├── IndexShopsRequest.php
└── IndexBookingsRequest.php
```

### View

```
resources/views/booker/
├── shops/
│   └── index.blade.php
├── profile/
│   └── edit.blade.php
└── bookings/
    ├── index.blade.php
    ├── create.blade.php
    └── show.blade.php
```

### ルーティング

```php
// routes/web.php

// 会員向け: 登録店舗一覧（店舗に依存しない）
Route::prefix('booker')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('shops', [Booker\ShopsController::class, 'index'])->name('booker.shops.index');
    });

// 会員向け: 店舗別機能
Route::prefix('shops/{shop:slug}/booker')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        // プロフィール
        Route::get('profile/edit', [Booker\ProfileController::class, 'edit'])->name('booker.profile.edit');
        Route::put('profile', [Booker\ProfileController::class, 'update'])->name('booker.profile.update');

        // 予約
        Route::get('bookings', [Booker\BookingController::class, 'index'])->name('booker.bookings.index');
        Route::get('bookings/create', [Booker\BookingController::class, 'create'])->name('booker.bookings.create');
        Route::post('bookings', [Booker\BookingController::class, 'store'])->name('booker.bookings.store');
        Route::get('bookings/{booking}', [Booker\BookingController::class, 'show'])->name('booker.bookings.show');
        Route::delete('bookings/{booking}', [Booker\BookingController::class, 'destroy'])->name('booker.bookings.destroy');
    });
```
