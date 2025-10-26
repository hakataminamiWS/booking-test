# オーナー機能仕様書

## 1. 目的

このドキュメントは、店舗オーナー (Owner) が利用する機能の UI/UX、API エンドポイント、およびデータインタラクションに関する詳細な仕様を定義します。

## 2. 対象ユーザー

-   Owner (店舗オーナー)

---

## 3. 主な画面と機能

本仕様書で定義する主な画面と、その機能概要は以下の通りです。

### 店舗管理

-   **店舗一覧画面 (`/owner/shops`)**: 自身が所有する店舗を一覧で確認し、新規登録や詳細画面へ遷移します。
-   **店舗登録画面 (`/owner/shops/create`)**: 契約の店舗数上限まで、新しい店舗を登録します。
-   **店舗詳細画面 (`/owner/shops/{shop}`)**: 店舗の基本情報、予約受付に関する設定を確認します。
-   **店舗詳細管理画面 (`/owner/shops/{shop}/edit`)**: 店舗の基本情報、予約受付に関する設定を編集します。
-   **営業時間一覧画面 (`/owner/shops/{shop}/business-hours`)**: 店舗の営業時間や定休日、特別営業日、特別休業日を表示します。
-   **営業時間・定休日編集画面 (`/owner/shops/{shop}/business-hours/regular/edit`)**: 店舗の営業時間や定休日を編集します。
-   **特別営業日登録画面 (`/owner/shops/{shop}/business-hours/special-open-days/create`)**: 店舗の特別営業日を登録します。
-   **特別営業日編集画面 (`/owner/shops/{shop}/business-hours/special-open-days/{id}/edit`)**: 店舗の特別営業日を編集します。
-   **特別休業日登録画面 (`/owner/shops/{shop}/business-hours/special-closed-days/create`)**: 店舗の特別休業日を登録します。
-   **特別休業日編集画面 (`/owner/shops/{shop}/business-hours/special-closed-days/{id}/edit`)**: 店舗の特別休業日を編集します。

### スタッフ管理

-   **スタッフ一覧画面 (`/owner/shops/{shop}/staffs`)**: 店舗に所属するスタッフを一覧で確認し画面へ遷移します。
-   **スタッフ登録画面 (`/owner/shops/{shop}/staffs/create`)**: 店舗に所属するスタッフを新規登録します。
-   **スタッフ詳細画面 (`/owner/shops/{shop}/staffs/{staff}`)**: 店舗に所属するスタッフの詳細情報を確認します。
-   **スタッフプロフィール編集画面 (`/owner/shops/{shop}/staffs/{staff}/edit`)**: 店舗に所属するスタッフのプロフィール情報を編集します。
-   **シフト一覧画面 (`/owner/shops/{shop}/staffs/{staff}/shifts`)**: 特定の店舗の、特定のスタッフのシフトを一覧表示します。
-   **シフト登録画面 (`/owner/shops/{shop}/staffs/{staff}/shifts/create`)**: 特定の店舗の、特定のスタッフのシフトを新規登録します。
-   **シフト詳細画面 (`/owner/shops/{shop}/staffs/{staff}/shifts/{shift}`)**: 特定の店舗の、特定のスタッフのシフトの詳細を表示します。
-   **シフト詳細管理画面 (`/owner/shops/{shop}/staffs/{staff}/shifts/{shift}/edit`)**: 特定の店舗の、特定のスタッフのシフトの詳細を編集します。

### メニュー管理

-   **メニュー一覧画面 (`/owner/shops/{shop}/menus`)**: 店舗で提供するメニューとオプションを一覧で確認します。
-   **メニュー登録・編集画面 (`/owner/shops/{shop}/menus/{menu}`)**: メニューやオプションの詳細を登録・編集します。

### 予約管理

-   **予約一覧画面 (`/owner/shops/{shop}/bookings`)**: 店舗の予約を一覧で確認し、検索や絞り込みを行います。
-   **予約詳細画面 (`/owner/shops/{shop}/bookings/{booking}`)**: 個別の予約内容を確認し、ステータスの変更などを行います。
-   **手動予約登録画面 (`/owner/shops/{shop}/bookings/create`)**: 管理画面から直接、新しい予約を登録します。

### 契約情報

-   **契約情報確認画面 (`/owner/contracts`)**: 自身の契約プラン（店舗数上限など）や契約期間を確認します。

### ダッシュボード

-   **ダッシュボード画面 (`/owner/dashboard`)**: ログイン後のトップページとして、店舗運営に関する重要情報を集約して表示します。

---

## 4. 機能・画面仕様詳細

### 店舗一覧画面

#### 機能概要

オーナーが自身のアカウントで作成した店舗を一覧表示し、管理するための基本画面です。ここから各店舗の詳細設定や新規店舗の登録へ進みます。
一覧画面は、`docs/phase-3/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された標準パターンで実装します。

#### 画面仕様詳細 (`/owner/shops`)

##### 表示項目一覧

-   ウィンドウタイトル: `店舗一覧`

-   登録店舗数セクション
    -   セクションタイトル: `登録店舗数`
    -   ページタイトルの近くに「登録店舗数: X / Y」の形式で、現在の店舗数と契約上の上限店舗数を表示します。
-   店舗一覧セクション
    -   セクションタイトル: `店舗一覧`
    -   **「店舗を新規作成する」ボタン**:
    -   セクションタイトルと横並びで配置します（ボタンは右寄せ）。モバイルビューの場合で、タイトルと重なる場合は、ページタイトルとボタンを積み上げて配置します。
    -   クリックすると店舗登録画面へ遷移します。
    -   **現在の店舗数が契約上限に達している場合、このボタンは非活性（disabled）状態になります。**

| カラム（ラベル） | 表示内容                                  | フィルタ        | ソート | 操作       |
| :--------------- | :---------------------------------------- | :-------------- | :----- | :--------- |
| 店舗 ID          | `shops.slug`                              | 可 (インプット) | 可     |            |
| 店舗名           | `shops.name`                              | 可 (インプット) | 可     |            |
| 受付状況         | `shops.accepts_online_bookings` (boolean) | 不可            | なし   |            |
| 登録日時         | `shops.created_at`                        | 不可            | なし   |            |
| 操作             | 「詳細」ボタン                            | 不可            | なし   | 詳細の表示 |

#### バックエンド仕様

##### ページ表示時のデータ（Web コントローラから渡す情報）

`Owner\ShopsController@index` は、Vue コンポーネントをマウントする Blade ビューを返します。その際、以下の情報を`props`として Vue コンポーネントに渡します。

| データ名            | 型        | 説明                               |
| :------------------ | :-------- | :--------------------------------- |
| `maxShops`          | `integer` | 契約で許可されている店舗数の上限。 |
| `currentShopsCount` | `integer` | 現在登録済みの店舗数。             |

##### API エンドポイント

-   **URL**: `GET /owner/api/shops`
-   **コントローラ**: `Api\Owner\ShopsController@index`
-   **リクエストクラス**: `App\Http\Requests\Api\Owner\IndexShopsRequest`
-   **クエリパラメータ**:
    -   `slug` (string): 店舗 ID（URL で利用する、URL セーフな一意の識別子）による部分一致検索。
    -   `name` (string): 店舗名による部分一致検索。
    -   `sort_by` (string): ソート対象カラム。許可される値は `slug`, `name`, `accepts_online_bookings`, `created_at`。
    -   `sort_order` (string): ソート方向 (`asc` or `desc`)。
    -   `page` (integer): ページ番号。
    -   `per_page` (integer): 1 ページあたりの表示件数。

##### 処理内容

-   ログインしているオーナーに紐づく `shops` テーブルのレコードを主軸とします。
-   `ShopPolicy@viewAny` により、認可されたデータのみが返却されることを保証します。
-   受け取ったクエリパラメータに基づき、動的なフィルタリング、ソーティングを適用します。
-   ページネーションを適用して結果を返却します。
-   「詳細」ボタンを押すと、対象店舗の詳細画面 (`/owner/shops/{shop-slug}`) に遷移します。

---

### 店舗登録画面

#### 機能概要

オーナーが、自身の契約プランで許可された店舗数の上限まで、新しい店舗を登録するための画面です。

#### 画面仕様詳細 (`/owner/shops/create`)

##### 入力項目一覧

タイムゾーンは、一律 `Asia/Tokyo` とし、入力不可の表示項目とします。これは、将来的に多言語・多地域対応を見据えた設計です。

-   ウィンドウタイトル: `店舗の新規登録`
-   ページタイトル: `店舗の新規登録`
-   フォーム全体は `v-card` で囲み、入力フィールドを配置します。
-   画面下部に「登録する」ボタンを配置します。

| ラベル                 | DB カラム                       | UI               | ヘルプテキスト                                                                             |
| :--------------------- | :------------------------------ | :--------------- | :----------------------------------------------------------------------------------------- |
| **店舗名**             | `name`                          | テキスト入力     | お客様に表示される店舗の正式名称を入力します。                                             |
| **店舗 ID**            | `slug`                          | テキスト入力     | 予約ページの URL に使われる半角英数字とハイフンのみの文字列です。例: `hakata-minami-store` |
| **予約枠の間隔**       | `time_slot_interval`            | セレクトボックス | カレンダーに表示する予約時間枠の刻みを分単位で選択します。(例: 15, **30**, 60)             |
| **予約承認方法**       | `booking_confirmation_type`     | ラジオボタン     | `automatic` (自動承認) または `manual` (手動承認) を選択します。                           |
| **オンライン予約受付** | `accepts_online_bookings`       | スイッチ         | お客様からのオンラインでの予約を受け付けるかどうかを設定します。                           |
| **タイムゾーン**       | `timezone`                      | 表示（入力不可） | 店舗が所在する地域のタイムゾーンを選択します。（ Asia/Tokyo）                              |
| **キャンセル期限**     | `cancellation_deadline_minutes` | 数値入力         | 予約の何分前までお客様によるキャンセルを許可するか設定します。(例: 1440 分 = 24 時間前)    |
| **予約締切**           | `booking_deadline_minutes`      | 数値入力         | 予約の何分前でオンライン予約の受付を締め切るか設定します。(0 は直前まで許可)               |

#### バックエンド仕様

##### データ受け渡し

-   **コントローラ**: `App\Http\Controllers\Owner\ShopsController@create`
-   **処理内容**:
    -   `ShopPolicy@create` を使用して、店舗作成が可能か（契約が有効か、上限に達していないか）を認可チェックします。
    -   認可に失敗した場合は、403 エラーページを表示します。
    -   成功した場合は、店舗登録フォームのビュー (`owner.shops.create`) を返します。
-   CSRF トークンを blade → Vue に渡す。
-   渡す方法は`docs/phase-2/ARCHITECTURE.md` の方法に従う。

##### フォーム送信

-   **ルート**: `POST /owner/shops`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopsController@store`
-   **リクエストクラス**: `App\Http\Requests\Owner\StoreShopRequest`

##### バリデーション

-   **ルール**:

    -   `name`: `required`, `string`, `max:255`
    -   `slug`: `required`, `string`, `max:255`, `unique:shops,slug`, `regex:/^[a-z0-9-]+$/`, `not_in:create,edit`
    -   `time_slot_interval`: `required`, `integer`
    -   `booking_confirmation_type`: `required`, `string`
    -   `accepts_online_bookings`: `required`, `boolean`
    -   `timezone`: `required`, `string`
    -   `cancellation_deadline_minutes`: `required`, `integer`, `min:0`
    -   `booking_deadline_minutes`: `required`, `integer`, `min:0`

-   フロントエンド:
    -   フォーム送信前に、Vue コンポーネント内で基本的なバリデーションを実行します。
    -   `slug`入力フィールドからフォーカスが外れたタイミングで、`POST /owner/api/shops/validate-slug` エンドポイントにリクエストを送信します。
    -   API のレスポンスに応じて、入力フィールドの下に「この ID は使用できます」または「この ID は既に使用されています」といったメッセージを表示します。
    -   重複している場合は、エラー状態として表示し、「登録する」ボタンを非活性化します。
    -   バリデーションエラーがある場合、送信を中止し、各入力フィールドの下にエラーメッセージを表示します。
-   バックエンド:
    -   `StoreShopRequest` でリクエストデータのバリデーションを実行します。
    -   バリデーションエラーがある場合、前の画面にリダイレクトし、エラーメッセージを表示します。

##### 処理内容

-   `ShopPolicy@create` を使用して、再度店舗作成が可能か（契約が有効か、上限に達していないか）認可チェックを行います。
-   `StoreShopRequest` でリクエストデータのバリデーションを実行します。
-   検証に成功した場合、`shops` テーブルに新しいレコードを作成します。`owner_user_id` には認証中ユーザーの ID を設定します。
-   登録完了後、店舗一覧画面 (`/owner/shops`) にリダイレクトし、「店舗を登録しました」という成功メッセージを表示します。

##### API エンドポイント

-   この画面は基本的なフォーム送信はサーバーサイドで完結しますが、ユーザー体験向上のため、**店舗 ID の重複チェックのみ非同期通信を行います。**

##### 店舗 ID 重複チェック API

-   **URL**: `POST /owner/api/shops/validate-slug`
-   **コントローラ**: `Api\Owner\ShopsController@validateSlug`
-   **リクエスト**: `{ "slug": "入力されたスラッグ" }`
-   **処理内容**:
    -   受け取った店舗 ID（`slug`）が、`shops`テーブルで既に使用されているかを検証します。
    -   結果を JSON で返却します。
-   **レスポンス**:
    -   **使用可能な場合**: `200 OK` `{ "is_valid": true }`
    -   **使用済みの場合**: `200 OK` `{ "is_valid": false, "message": "このIDは既に使用されています。" }`
    -   **バリデーションエラーの場合**: `422 Unprocessable Entity`

---

### 店舗詳細画面

#### 機能概要

オーナーが個別の店舗情報を確認し、関連する設定（営業時間、スタッフ管理など）への起点となる画面です。
この画面から編集モードに切り替えることで、店舗の基本情報を更新できます。

#### 画面仕様詳細 (`/owner/shops/{shop}`)

-   ウィンドウタイトル: `店舗詳細: {店舗名}`

#### 表示項目一覧

リンクセクション：

-   店舗一覧画面へのリンクをページ上部に表示

店舗ヘッダー：

-   店舗：{店舗名} (店舗 ID: {店舗 ID})
    これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

店舗詳細セクション：

-   セクションタイトル: `店舗詳細: {店舗名}`

-   セクションタイトルと横並びで「店舗詳細を編集する」ボタンを配置する。ボタンは右寄せで配置。モバイルビューの場合で、タイトルと重なる場合は、セクションタイトル、「店舗詳細を編集する」ボタンの積み上げ配置

店舗の基本的な設定情報を読み取り専用で表示します。

| 項目               | データソース                          | 表示形式                            |
| :----------------- | :------------------------------------ | :---------------------------------- |
| 店舗名             | `shops.name`                          | テキスト                            |
| 店舗 ID            | `shops.slug`                          | テキスト                            |
| 予約枠の間隔       | `shops.time_slot_interval`            | テキスト（例: "30 分"）             |
| 予約承認方法       | `shops.booking_confirmation_type`     | テキスト（例: "自動承認"）          |
| オンライン予約受付 | `shops.accepts_online_bookings`       | テキスト（例: "受付中" / "停止中"） |
| タイムゾーン       | `shops.timezone`                      | テキスト                            |
| キャンセル期限     | `shops.cancellation_deadline_minutes` | テキスト（例: "1440 分前"）         |
| 予約締切           | `shops.booking_deadline_minutes`      | テキスト（例: "0 分前"）            |
| 登録日時           | `shops.created_at`                    | 日時                                |
| 更新日時           | `shops.updated_at`                    | 日時                                |

#### バックエンド仕様

##### ページ表示 (Web コントローラ)

-   **ルート**: `GET /owner/shops/{shop}` (ルートモデルバインディングを利用)
-   **コントローラ**: `App\Http\Controllers\Owner\ShopsController@show`
-   **認可**: `ShopPolicy@view` を使用し、認証中のオーナーが自身の店舗のみを閲覧できるよう制限します。
-   **処理内容**:
    1.  ルートモデルバインディングによって、URL の `{shop}` に該当する `Shop` モデルインスタンスを取得します。
    2.  取得した `Shop` オブジェクトを `owner.shops.show` ビューに渡します。
    3.  ビューは、受け取った `Shop` オブジェクトを `props` として Vue コンポーネントに渡してマウントします。

##### API エンドポイント

-   店舗詳細画面の表示はサーバーサイドで完結するため、API エンドポイントは不要です。

---

### 店舗詳細管理画面

#### 機能概要

オーナーが既存の店舗の基本情報（店舗名、予約枠の間隔など）を更新するための画面です。

#### 画面仕様詳細 (`/owner/shops/{shop}/edit`)

-   **ウィンドウタイトル**: `店舗情報編集: {店舗名}`

#### 表示項目一覧

リンクセクション：

-   「店舗詳細に戻る」のリンクをページ上部に表示

店舗ヘッダー：

-   店舗：{店舗名} (店舗 ID: {店舗 ID})
    これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

店舗情報編集セクション：

-   フォーム全体は `v-card` で囲みます。
-   カードタイトルは「店舗情報編集」とします。
-   カードの最下部に「削除する」「更新する」ボタンを配置します。

##### フォーム項目一覧

| ラベル                 | UI               | `name`属性                      | 備考                                     |
| :--------------------- | :--------------- | :------------------------------ | :--------------------------------------- |
| **店舗名**             | テキスト入力     | `name`                          | 必須。                                   |
| **店舗 ID**            | テキスト入力     | `slug`                          | **読み取り専用（編集不可）**。           |
| **予約枠の間隔**       | セレクトボックス | `time_slot_interval`            | 選択肢: 15, 30, 60。必須。               |
| **予約承認方法**       | ラジオボタン     | `booking_confirmation_type`     | 選択肢: 自動承認, 手動承認。必須。       |
| **オンライン予約受付** | ラジオボタン     | `accepts_online_bookings`       | 選択肢: 受け付ける, 受け付けない。必須。 |
| **タイムゾーン**       | テキスト入力     | `timezone`                      | **読み取り専用（編集不可）**。           |
| **キャンセル期限**     | 数値入力         | `cancellation_deadline_minutes` | 単位は分。必須。                         |
| **予約締切**           | 数値入力         | `booking_deadline_minutes`      | 単位は分。必須。                         |

---

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/edit`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopsController@edit`
-   **処理内容**:
    -   `ShopPolicy@update` を使用し、オーナーが自身の店舗情報を編集できるか認可チェックを行います。
    -   認可に失敗した場合は、403 エラーページを表示します。
    -   成功した場合は、ルートモデルバインディングで取得した`Shop`オブジェクトを `owner.shops.edit` ビューに渡します。
-   CSRF トークンを blade → Vue に渡す。
-   渡す方法は`docs/phase-2/ARCHITECTURE.md` の方法に従う。

##### フォーム送信 (更新処理)

-   **ルート**: `PUT /owner/shops/{shop:slug}`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopsController@update`
-   **リクエストクラス**: `App\Http\Requests\Owner\UpdateShopRequest`

##### バリデーション

-   **フロントエンド**:
    -   フォーム送信前に、必須項目が入力されているかなどの基本的なチェックを実行します。
-   **バックエンド (`UpdateShopRequest`)**:
    -   **認可**: `authorize`メソッド内で`ShopPolicy@update`を呼び出します。
    -   **ルール**:
        -   `name`: `required`, `string`, `max:255`
        -   `time_slot_interval`: `required`, `integer`
        -   `booking_confirmation_type`: `required`, `string`
        -   `accepts_online_bookings`: `required`, `boolean`
        -   `cancellation_deadline_minutes`: `required`, `integer`, `min:0`
        -   `booking_deadline_minutes`: `required`, `integer`, `min:0`

##### 処理内容

1.  `UpdateShopRequest` で認可とバリデーションを実行します。
2.  バリデーションが成功した場合、検証済みのデータを使って `Shop` モデルの情報を更新します。
3.  更新後、店舗詳細画面 (`/owner/shops/{shop:slug}`) にリダイレクトし、成功メッセージを表示します。
4.  バリデーションが失敗した場合、Laravel が自動的に編集画面にリダイレクトし、エラーメッセージを表示します。

##### API エンドポイント

-   この画面はサーバーサイドで完結するため、データ取得や更新のための API エンドポイントは提供しません。

### 営業時間一覧画面

#### 機能概要

オーナーが店舗の営業スケジュール全体を俯瞰し、各種設定画面へ遷移するためのハブとなる画面です。通常営業時間、特別営業日、特別休業日の 3 つの情報をまとめて表示します。

#### 画面仕様詳細 (`/owner/shops/{shop}/business-hours`)

-   **ウィンドウタイトル**: `営業時間一覧`

##### 表示項目一覧

リンクセクション：

-   営業時間一覧画面へのリンクをページ上部に表示（これは、自分自身へのリンク。将来的に変更予定。枠だけ確保）

店舗ヘッダー：

-   店舗：{店舗名} (店舗 ID: {店舗 ID})
    これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

通常営業時間・定休日セクション

-   **セクションタイトル**: `通常営業時間・定休日`
-   **ボタン**:
    -   セクションタイトルの右側に「編集する」ボタンを配置します。
    -   クリックすると営業時間・定休日編集画面 (`/owner/shops/{shop:slug}/business-hours/regular/edit`) へ遷移します。
-   **表示内容**:
    -   曜日（日曜〜土曜）ごとに、設定された「ステータス（営業日/定休日）」と「営業時間（例: 09:00 - 18:00）」をテーブル形式で表示します。

| 曜日 | ステータス | 営業時間      |
| :--- | :--------- | :------------ |
| 日曜 | 定休日     | -             |
| 月曜 | 営業日     | 09:00 - 18:00 |
| ...  | ...        | ...           |

特別営業日セクション

-   **セクションタイトル**: `特別営業日`
-   **ボタン**:
    -   セクションタイトルの右側に「新規登録」ボタンを配置します。
    -   クリックすると特別営業日登録画面 (`/owner/shops/{shop:slug}/business-hours/special-open-days/create`) へ遷移します。
-   **表示内容**:
    -   登録済みの特別営業日をテーブル形式で一覧表示します。
    -   テーブル形式では、ソートやフィルタの機能は提供しません。
    -   表示するデータは、日付が表示現在の日付か未来の日付のみ表示します。過去のデータは表示しません。
    -   **表示項目**: 日付, 営業時間（例: 09:00 - 18:00）, 営業日名, 操作
    -   **操作**: 各行に「編集」ボタンを配置します。
        -   編集ボタンの遷移先: `/owner/shops/{shop:slug}/business-hours/special-open-days/{id}/edit`

特別休業日セクション

-   **セクションタイトル**: `特別休業日`
-   **ボタン**:
    -   セクションタイトルの右側に「新規登録」ボタンを配置します。
    -   クリックすると特別休業日登録画面 (`/owner/shops/{shop:slug}/business-hours/special-closed-days/create`) へ遷移します。
-   **表示内容**:
    -   登録済みの特別休業日をテーブル形式で一覧表示します。
    -   テーブル形式では、ソートやフィルタの機能は提供しません。
    -   表示するデータは、日付が表示現在の日付か未来の日付のみ表示します。過去のデータは表示しません。
    -   **表示項目**: 期間 (開始日〜終了日), 休業日名, 操作
    -   **操作**: 各行に「編集」ボタンを配置します。
        -   編集ボタンの遷移先: `/owner/shops/{shop:slug}/business-hours/special-closed-days/{id}/edit`

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/business-hours`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopBusinessHoursController@index`
-   **処理内容**:
    1.  `ShopPolicy@view` を使用し、オーナーが自身の店舗情報のみを閲覧できるか認可チェックを行います。
    2.  ルートモデルバインディングで `Shop` オブジェクトを取得します。
    3.  対象店舗に紐づく以下の情報を取得します。
        -   `shop_business_hours_regular` (全 7 曜日分)
        -   `shop_special_open_days` (日付の昇順でソートされた、現在日付を含む以降の全件)
        -   `shop_special_closed_days` (開始日の昇順でソートされた、現在日付を含む以降の全件)
    4.  取得したすべてのデータを `owner.shops.business-hours.index` ビューに渡します。
    5.  ビューは、受け取ったデータを `props` として Vue コンポーネントに渡してマウントします。

##### API エンドポイント

-   この画面の初期表示はサーバーサイドで完結するため、データ取得用の API エンドポイントは不要です。

---

### 営業時間・定休日編集画面

#### 機能概要

店舗オーナーが、店舗の基本的な営業時間と定休日（毎週の繰り返し）を設定・更新するための画面です。
店舗の営業時間と定休日はスタッフのシフト登録時のチェックのみに使用され、予約受付の可否には影響しません。

#### 画面仕様詳細 (`/owner/shops/{shop}/business-hours/regular/edit`)

    -   **ウィンドウタイトル**: `営業時間・定休日設定`

##### 表示項目一覧

    リンクセクション：

    -   営業時間一覧画面へのリンクをページ上部に表示

    店舗ヘッダー：
    -   店舗：{店舗名} (店舗 ID: {店舗ID})
    これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

    店舗詳細セクション：

    -   **セクションタイトル**: `営業時間・定休日設定`

    -   フォーム全体は `v-card` で囲みます。
    -   カードの最下部に「設定する」ボタンを配置します。

##### フォーム項目一覧

| ラベル             | UI               | `name`属性                      | 備考                                                                                             |
| :----------------- | :--------------- | :------------------------------ | :----------------------------------------------------------------------------------------------- |
| **タイムゾーン**   | テキスト入力     | `timezone`                      | **読み取り専用（編集不可）**。                                                                   |
| **曜日ごとの設定** | -                | `business_hours`                | 以下を日曜日から土曜日まで 7 行分表示します。                                                    |
| (各曜日)           | チェックボックス | `business_hours[N][is_open]`    | **営業日**の場合はチェックを ON にします。OFF の場合は定休日となり、時間入力が非活性化されます。 |
| (各曜日)           | 時間入力         | `business_hours[N][start_time]` | 営業開始時刻を入力します。                                                                       |
| (各曜日)           | 時間入力         | `business_hours[N][end_time]`   | 営業終了時刻を入力します。                                                                       |

---

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/business-hours/regular/edit`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopBusinessHoursController@edit`
-   **処理内容**:
    -   `ShopPolicy@update` を使用し、オーナーが自身の店舗情報を編集できるか認可チェックを行います。
    -   認可に失敗した場合は、403 エラーページを表示します。
    -   成功した場合は、対象店舗の `shop_business_hours_regular` テーブルから曜日ごと（`day_of_week` が 0〜6）のレコードを 7 件取得します。レコードが存在しない曜日は、`is_open=false` などのデフォルト値で補完します。
    -   取得した 7 件の営業時間データを `owner.shops.business-hours.regular.edit` ビューに渡します。
    -   CSRF トークンを Blade から Vue へ渡します。（`docs/phase-3/ARCHITECTURE.md` の方式に従う）

##### フォーム送信 (更新処理)

-   **ルート**: `PUT /owner/shops/{shop:slug}/business-hours/regular`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopBusinessHoursController@update`
-   **リクエストクラス**: `App\Http\Requests\Owner\UpdateShopBusinessHoursRequest`

##### バリデーション

-   **フロントエンド**:
    -   フォーム送信前に、営業日（`is_open`が`true`）の場合に開始・終了時刻が入力されているかチェックします。
    -   終了時刻が開始時刻より後になっているかチェックします。
-   **バックエンド (`UpdateShopBusinessHoursRequest`)**:
    -   **認可**: `authorize`メソッド内で`ShopPolicy@update`を呼び出します。
    -   **ルール**:
        -   `business_hours`: `required`, `array`, `size:7`
        -   `business_hours.*.day_of_week`: `required`, `integer`, `between:0,6`
        -   `business_hours.*.is_open`: `required`, `boolean`
        -   `business_hours.*.start_time`: `required_if:business_hours.*.is_open,true`, `nullable`, `date_format:H:i`
        -   `business_hours.*.end_time`: `required_if:business_hours.*.is_open,true`, `nullable`, `date_format:H:i`, `after:business_hours.*.start_time`

##### 処理内容

1.  `UpdateShopBusinessHoursRequest` で認可とバリデーションを実行します。
2.  バリデーションが成功した場合、受け取った 7 日分のデータでループ処理を行います。
3.  `shop_business_hours_regular` テーブルに対して、複合キーである `['shop_id', 'day_of_week']` を使用して `updateOrCreate` を実行し、各曜日の設定を更新または新規作成します。
4.  更新後、営業時間一覧画面 (`/owner/shops/{shop:slug}/business-hours`) にリダイレクトし、「営業時間を更新しました」という成功メッセージを表示します。

##### API エンドポイント

-   この画面はサーバーサイドで完結するため、データ取得や更新のための API エンドポイントは提供しません。

### 特別営業日登録画面

#### 機能概要

オーナーが、定休日とは別に、特別に営業する日とその時間帯を登録するための画面です。

#### 画面仕様詳細 (`/owner/shops/{shop}/business-hours/special-open-days/create`)

    -   **ウィンドウタイトル**: `特別営業日登録`

##### 表示項目一覧

    リンクセクション：

    -   営業時間一覧画面へのリンクをページ上部に表示

    店舗ヘッダー：
    -   店舗：{店舗名} (店舗 ID: {店舗ID})
    これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

    店舗詳細セクション：

    -   **セクションタイトル**: `特別営業日登録`

    -   フォーム全体は `v-card` で囲みます。
    -   カードの最下部に「登録する」ボタンを配置します。

##### フォーム項目一覧

| ラベル       | UI           | `name`属性   | 備考                               |
| :----------- | :----------- | :----------- | :--------------------------------- |
| **日付**     | 日付入力     | `date`       | 必須。                             |
| **開始時刻** | 時刻入力     | `start_time` | 必須。タイムピッカーを利用。       |
| **終了時刻** | 時刻入力     | `end_time`   | 必須。タイムピッカーを利用。       |
| **営業日名** | テキスト入力 | `name`       | 任意入力。（例：「祝日特別営業」） |

---

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/business-hours/special-open-days/create`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopSpecialOpenDaysController@create`
-   **処理内容**:
    -   `ShopPolicy@update` を使用し、オーナーが自身の店舗情報を編集できるか認可チェックを行います。
    -   成功した場合は、`owner.shops.business-hours.special-open-days.create` ビューを返します。

##### フォーム送信 (登録処理)

-   **ルート**: `POST /owner/shops/{shop:slug}/business-hours/special-open-days`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopSpecialOpenDaysController@store`
-   **リクエストクラス**: `App\Http\Requests\Owner\StoreShopSpecialOpenDayRequest`

##### バリデーション (`StoreShopSpecialOpenDayRequest`)

-   **認可**: `authorize`メソッド内で`ShopPolicy@update`を呼び出します。
-   **ルール**:
    -   `name`: `nullable`, `string`, `max:255`
    -   `date`: `required`, `date`
    -   `start_time`: `required`, `date_format:H:i`
    -   `end_time`: `required`, `date_format:H:i`, `after:start_time`

##### 処理内容

1.  `StoreShopSpecialOpenDayRequest` で認可とバリデーションを実行します。
2.  バリデーションが成功した場合、`shop_special_open_days` テーブルに新しいレコードを作成します。
3.  登録後、営業時間一覧画面 (`/owner/shops/{shop:slug}/business-hours`) にリダイレクトし、「特別営業日を登録しました」という成功メッセージを表示します。

---

### 特別営業日編集画面

#### 機能概要

オーナーが、登録済みの特別営業日の内容（日付、時間、名称）を修正するための画面です。

#### 画面仕様詳細 (`/owner/shops/{shop}/business-hours/special-open-days/{id}/edit`)

-   **ウィンドウタイトル**: `特別営業日編集`

##### 表示項目一覧

    リンクセクション：

    -   営業時間一覧画面へのリンクをページ上部に表示

    店舗ヘッダー：
    -   店舗：{店舗名} (店舗 ID: {店舗ID})
    これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

    フォームセクション：

    -   **セクションタイトル**: `特別営業日編集`
    -   フォーム全体は `<v-card>` で囲みます。
    -   フォーム項目は、登録済みのデータが初期表示されます。
    -   カードの最下部に「更新する」ボタンを配置します。

##### フォーム項目一覧

| ラベル       | UI           | `name`属性   | 備考                               |
| :----------- | :----------- | :----------- | :--------------------------------- |
| **日付**     | 日付入力     | `date`       | 必須。                             |
| **開始時刻** | 時刻入力     | `start_time` | 必須。タイムピッカーを利用。       |
| **終了時刻** | 時刻入力     | `end_time`   | 必須。タイムピッカーを利用。       |
| **営業日名** | テキスト入力 | `name`       | 任意入力。（例：「祝日特別営業」） |

---

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/business-hours/special-open-days/{special_open_day}/edit`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopSpecialOpenDaysController@edit`
-   **処理内容**:
    -   `ShopPolicy@update` を使用し、オーナーが自身の店舗情報を編集できるか認可チェックを行います。
    -   ルートモデルバインディングで取得した `Shop` と `ShopSpecialOpenDay` のオブジェクトを `owner.shops.business-hours.special-open-days.edit` ビューに渡します。
    -   CSRF トークン等を Blade から Vue へ渡します。

##### フォーム送信 (更新処理)

-   **ルート**: `PUT /owner/shops/{shop:slug}/business-hours/special-open-days/{special_open_day}`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopSpecialOpenDaysController@update`
-   **リクエストクラス**: `App\Http\Requests\Owner\UpdateShopSpecialOpenDayRequest`

##### バリデーション

-   **フロントエンド**:
    -   フォーム送信前に、必須項目が入力されているか、終了時刻が開始時刻より後になっているか等の基本的なチェックを実行します。
-   **バックエンド (`UpdateShopSpecialOpenDayRequest`)**:
    -   **認可**: `authorize`メソッド内で`ShopPolicy@update`を呼び出します。
    -   **ルール**:
        -   `date`: `required`, `date`
        -   `start_time`: `required`, `date_format:H:i`
        -   `end_time`: `required`, `date_format:H:i`, `after:start_time`
        -   `name`: `nullable`, `string`, `max:255`

##### 処理内容

1.  `UpdateShopSpecialOpenDayRequest` で認可とバリデーションを実行します。
2.  バリデーションが成功した場合、対象の `ShopSpecialOpenDay` モデルの情報を更新します。
3.  更新後、営業時間一覧画面 (`/owner/shops/{shop:slug}/business-hours`) にリダイレクトし、「特別営業日を更新しました」という成功メッセージを表示します。

##### API エンドポイント

-   この画面はサーバーサイドで完結するため、データ取得や更新のための API エンドポイントは提供しません。

---

### 特別休業日登録画面

#### 機能概要

オーナーが、店舗を特別に休業する期間（夏季休業など）を登録するための画面です。

#### 画面仕様詳細 (`/owner/shops/{shop}/business-hours/special-closed-days/create`)

-   **ウィンドウタイトル**: `特別休業日登録`

##### 表示項目一覧

    リンクセクション：

    -   営業時間一覧画面へのリンクをページ上部に表示

    店舗ヘッダー：

    -   店舗：{店舗名} (店舗 ID: {店舗 ID})
        これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

    店舗詳細セクション：

    -   **セクションタイトル**: `特別休業日登録`
    -   フォーム全体は `<v-card>` で囲みます。
    -   カードの最下部に「登録する」ボタンを配置します。

##### フォーム項目一覧

| ラベル       | UI           | `name`属性 | 備考                           |
| :----------- | :----------- | :--------- | :----------------------------- |
| **開始日**   | 日付入力     | `start_at` | 必須。                         |
| **終了日**   | 日付入力     | `end_at`   | 必須。                         |
| **休業日名** | テキスト入力 | `name`     | 任意入力。（例：「夏季休業」） |

---

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/business-hours/special-closed-days/create`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopSpecialClosedDaysController@create`
-   **処理内容**:
    -   `ShopPolicy@update` を使用し、オーナーが自身の店舗情報を編集できるか認可チェックを行います。
    -   成功した場合は、`owner.shops.business-hours.special-closed-days.create` ビューを返します。

##### フォーム送信 (登録処理)

-   **ルート**: `POST /owner/shops/{shop:slug}/business-hours/special-closed-days`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopSpecialClosedDaysController@store`
-   **リクエストクラス**: `App\Http\Requests\Owner\StoreShopSpecialClosedDayRequest`

##### バリデーション (`StoreShopSpecialClosedDayRequest`)

-   **認可**: `authorize`メソッド内で`ShopPolicy@update`を呼び出します。
-   **ルール**:
    -   `name`: `nullable`, `string`, `max:255`
    -   `start_at`: `required`, `date`
    -   `end_at`: `required`, `date`, `after_or_equal:start_at`

##### 処理内容

1.  `StoreShopSpecialClosedDayRequest` で認可とバリデーションを実行します。
2.  バリデーションが成功した場合、`shop_special_closed_days` テーブルに新しいレコードを作成します。
3.  登録後、営業時間一覧画面 (`/owner/shops/{shop:slug}/business-hours`) にリダイレクトし、「特別休業日を登録しました」という成功メッセージを表示します。

### 特別休業日編集画面

#### 機能概要

オーナーが、登録済みの特別休業日の内容（期間、名称）を修正するための画面です。

#### 画面仕様詳細 (`/owner/shops/{shop}/business-hours/special-closed-days/{id}/edit`)

-   **ウィンドウタイトル**: `特別休業日編集`

##### 表示項目一覧

    リンクセクション：

    -   営業時間一覧画面へのリンクをページ上部に表示

    店舗ヘッダー：
    -   店舗：{店舗名} (店舗 ID: {店舗ID})
    これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

    フォームセクション：

    -   **セクションタイトル**: `特別休業日編集`
    -   フォーム全体は `<v-card>` で囲みます。
    -   フォーム項目は、登録済みのデータが初期表示されます。
    -   カードの最下部に「更新する」ボタンを配置します。

##### フォーム項目一覧

| ラベル       | UI           | `name`属性 | 備考                           |
| :----------- | :----------- | :--------- | :----------------------------- |
| **開始日**   | 日付入力     | `start_at` | 必須。                         |
| **終了日**   | 日付入力     | `end_at`   | 必須。                         |
| **休業日名** | テキスト入力 | `name`     | 任意入力。（例：「夏季休業」） |

---

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/business-hours/special-closed-days/{special_closed_day}/edit`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopSpecialClosedDaysController@edit`
-   **処理内容**:
    -   `ShopPolicy@update` を使用し、オーナーが自身の店舗情報を編集できるか認可チェックを行います。
    -   ルートモデルバインディングで取得した `Shop` と `ShopSpecialClosedDay` のオブジェクトを `owner.shops.business-hours.special-closed-days.edit` ビューに渡します。
    -   CSRF トークン等を Blade から Vue へ渡します。

##### フォーム送信 (更新処理)

-   **ルート**: `PUT /owner/shops/{shop:slug}/business-hours/special-closed-days/{special_closed_day}`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopSpecialClosedDaysController@update`
-   **リクエストクラス**: `App\Http\Requests\Owner\UpdateShopSpecialClosedDayRequest`

##### バリデーション

-   **フロントエンド**:
    -   フォーム送信前に、必須項目が入力されているか、終了日が開始日以降になっているか等の基本的なチェックを実行します。
-   **バックエンド (`UpdateShopSpecialClosedDayRequest`)**:
    -   **認可**: `authorize`メソッド内で`ShopPolicy@update`を呼び出します。
    -   **ルール**:
        -   `name`: `nullable`, `string`, `max:255`
        -   `start_at`: `required`, `date`
        -   `end_at`: `required`, `date`, `after_or_equal:start_at`

##### 処理内容

1.  `UpdateShopSpecialClosedDayRequest` で認可とバリデーションを実行します。
2.  バリデーションが成功した場合、対象の `ShopSpecialClosedDay` モデルの情報を更新します。
3.  更新後、営業時間一覧画面 (`/owner/shops/{shop:slug}/business-hours`) にリダイレクトし、「特別休業日を更新しました」という成功メッセージを表示します。

##### API エンドポイント

-   この画面はサーバーサイドで完結するため、データ取得や更新のための API エンドポイントは提供しません。

---

### 4.2. スタッフ管理

### スタッフ登録申し込み一覧画面

#### 機能概要

オーナーが店舗へのスタッフ登録申し込みを一覧で確認し、承認・却下を行うための画面です。
一覧画面は、`docs/phase-3/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された標準パターンで実装します。

#### 画面仕様詳細 (`/owner/shops/{shop}/staff-applications`)

##### 表示項目一覧

    リンクセクション：

    -   店舗詳細画面へのリンクをページ上部に表示

    店舗ヘッダー：
    -   店舗：{店舗名} (店舗 ID: {店舗ID})
    これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。


    スタッフ登録申し込み一覧セクション：
    -   セクションタイトル: `スタッフ登録申し込み一覧`
    -   この画面から新規作成は行わないため、「新規作成」ボタンは表示しません。

| カラム（ラベル） | 表示内容                             | フィルタ        | ソート | 操作          |
| :--------------- | :----------------------------------- | :-------------- | :----- | :------------ |
| 申込 ID          | `shop_staff_applications.id`         | 可 (インプット) | 可     |               |
| 申込者名         | `shop_staff_applications.name`       | 可 (インプット) | 可     |               |
| ステータス       | `shop_staff_applications.status`     | 可 (セレクト)   | 可     |               |
| 申込日時         | `shop_staff_applications.created_at` | 不可            | 可     |               |
| 操作             | 「承認」「却下」ボタン               | 不可            | なし   | 承認/却下処理 |

#### バックエンド仕様

##### ページ表示時のデータ（Web コントローラから渡す情報）

`Owner\ShopStaffApplicationController@index` は、Vue コンポーネントをマウントする Blade ビューを返します。その際、以下の情報を`props`として Vue コンポーネントに渡します。

| データ名 | 型       | 説明             |
| :------- | :------- | :--------------- |
| `shop`   | `object` | 現在の店舗情報。 |

##### API エンドポイント

-   **URL**: `GET /owner/api/shops/{shop}/staff-applications`
-   **コントローラ**: `Api\Owner\ShopStaffApplicationController@index`
-   **リクエストクラス**: `App\Http\Requests\Api\Owner\IndexShopStaffApplicationsRequest`
-   **クエリパラメータ**:
    -   `id` (integer): 申込 ID による完全一致検索。
    -   `name` (string): 申込者名による部分一致検索。
    -   `status` (string): ステータスによる検索 (`pending`, `approved`, `rejected`)。
    -   `sort_by` (string): ソート対象カラム。許可される値は `id`, `name`, `status`, `created_at`。
    -   `sort_order` (string): ソート方向 (`asc` or `desc`)。
    -   `page` (integer): ページ番号。
    -   `per_page` (integer): 1 ページあたりの表示件数。

##### フォーム送信（承認）

Laravel のフォームメソッドフィールドを利用して、`PUT` メソッドで送信します。一覧画面の各行に表示されるボタンから直接送信されます。

-   **送信先**: `PUT /owner/shops/{shop}/staff-applications/{staff_application}/approve`
-   **コントローラ**: `Owner\ShopStaffApplicationController@approve`
-   **処理内容**: `shop_staff_applications` テーブルの対象レコードの `status` を `approved` に更新します。同時に、`shop_staffs`テーブルにスタッフ情報を登録（または更新）し、`shop_staff_profiles`テーブルには申し込み時の名前を`nickname`として登録（または更新）します。処理完了後、一覧画面へリダイレクトします。

##### フォーム送信（却下）

Laravel のフォームメソッドフィールドを利用して、`PUT` メソッドで送信します。一覧画面の各行に表示されるボタンから直接送信されます。

-   **送信先**: `PUT /owner/shops/{shop}/staff-applications/{staff_application}/reject`
-   **コントローラ**: `Owner\ShopStaffApplicationController@reject`
-   **処理内容**: `shop_staff_applications` テーブルの対象レコードの `status` を `rejected` に更新し、一覧画面へリダイレクトします。

##### 処理内容

-   `ShopStaffApplicationPolicy@viewAny` により、認可されたデータのみが返却されることを保証します。
-   ログインしているオーナーの、URL で指定された店舗に紐づく `shop_staff_applications` テーブルのレコードを返却します。
-   受け取ったクエリパラメータに基づき、動的なフィルタリング、ソーティングを適用します。
-   ページネーションを適用して結果を返却します。

---

### スタッフ一覧画面

#### 機能概要

オーナーが店舗に所属するスタッフを一覧表示し、管理するための基本画面です。ここから各スタッフの詳細設定や新規スタッフの登録へ進みます。
一覧画面は、`docs/phase-3/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された標準パターンで実装します。

#### 画面仕様詳細 (`/owner/shops/{shop}/staffs`)

##### 表示項目一覧

    リンクセクション：

    -   店舗詳細画面へのリンクをページ上部に表示

    店舗ヘッダー：
    -   店舗：{店舗名} (店舗 ID: {店舗ID})
    これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

-   スタッフ一覧セクション
    -   セクションタイトル: `スタッフ一覧`
    -   **「予約枠用スタッフを登録する」ボタン**:
        -   セクションタイトルと横並びで配置します（ボタンは右寄せ）。
        -   クリックするとスタッフ登録画面へ遷移します。

| カラム（ラベル） | 表示内容                                     | フィルタ        | ソート | 操作           |
| :--------------- | :------------------------------------------- | :-------------- | :----- | :------------- |
| スタッフ ID      | `shop_staffs.id`                             | 可 (インプット) | 可     |                |
| ニックネーム     | `shop_staff_profiles.nickname`               | 可 (インプット) | 可     |                |
| 担当者/予約枠    | `shop_staffs.user_id` の有無 (担当者/予約枠) | 可 (セレクト)   | 可     |                |
| 登録日時         | `shop_staffs.created_at`                     | 不可            | 可     |                |
| 操作             | 「プロフィールを変更する」ボタン             | 不可            | なし   | 詳細画面へ遷移 |

#### バックエンド仕様

##### ページ表示時のデータ（Web コントローラから渡す情報）

`Owner\ShopStaffController@index` は、Vue コンポーネントをマウントする Blade ビューを返します。その際、以下の情報を`props`として Vue コンポーネントに渡します。

| データ名 | 型       | 説明             |
| :------- | :------- | :--------------- |
| `shop`   | `object` | 現在の店舗情報。 |

##### API エンドポイント

-   **URL**: `GET /owner/api/shops/{shop}/staffs`
-   **コントローラ**: `Api\Owner\ShopStaffController@index`
-   **リクエストクラス**: `App\Http\Requests\Api\Owner\IndexShopStaffsRequest`
-   **クエリパラメータ**:
    -   `id` (integer): スタッフ ID による完全一致検索。
    -   `nickname` (string): ニックネームによる部分一致検索。
    -   `type` (string): 種別による検索 (`user`, `frame`)。`user`は担当者、`frame`は予約枠に対応。
    -   `sort_by` (string): ソート対象カラム。許可される値は `id`, `nickname`, `type`, `created_at`。
    -   `sort_order` (string): ソート方向 (`asc` or `desc`)。
    -   `page` (integer): ページ番号。
    -   `per_page` (integer): 1 ページあたりの表示件数。

##### 処理内容

-   `ShopStaffPolicy@viewAny` により、認可されたデータのみが返却されることを保証します。
-   ログインしているオーナーの、URL で指定された店舗に紐づく `shop_staffs` テーブルのレコードを主軸とします。
-   `shop_staff_profiles` テーブルをリレーションで結合します。
-   `type` パラメータに応じて、`user_id` が `NOT NULL` (user) または `NULL` (frame) のレコードを絞り込みます。
-   受け取ったクエリパラメータに基づき、動的なフィルタリング、ソーティングを適用します。
-   ページネーションを適用して結果を返却します。
-   「プロフィールを編集する」ボタンを押すと、対象スタッフのプロフィール編集画面 (`/owner/shops/{shop}/staffs/{staff}/edit`) に遷移します。

---

### スタッフプロフィール編集

#### 機能概要

オーナーがスタッフのプロフィール情報（ニックネーム、プロフィール画像など）を編集するための画面です。

#### 画面仕様詳細 (`/owner/shops/{shop:slug}/staffs/{staff}/edit`)

-   **ウィンドウタイトル**: `プロフィール編集: {スタッフ名}`

    リンクセクション：

    -   「スタッフ一覧に戻る」へのリンクをページ上部に表示

    店舗ヘッダー：

    -   店舗：{店舗名} (店舗 ID: {店舗 ID})
        これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

    プロフィール編集セクション：

    -   **フォーム**:
    -   フォーム全体は `v-card` で囲みます。
    -   カードタイトルは「プロフィール編集」とします。
    -   カードの最下部に「削除する」「更新する」ボタンを配置します。

##### フォーム項目一覧

| ラベル                   | UI           | `name`属性        | 備考                                         |
| :----------------------- | :----------- | :---------------- | :------------------------------------------- |
| **ニックネーム**         | テキスト入力 | `nickname`        | 必須。                                       |
| **プロフィール画像(小)** | テキスト入力 | `small_image_url` | URL 形式。ファイルアップロードは将来の課題。 |
| **プロフィール画像(大)** | テキスト入力 | `large_image_url` | URL 形式。ファイルアップロードは将来の課題。 |

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/staffs/{staff}/edit`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopStaffController@edit`
-   **処理内容**:
    -   `ShopStaffPolicy@update` を使用し、オーナーがこのスタッフ情報を編集できるか認可チェックを行います。
    -   ルートモデルバインディングで取得した`ShopStaff`オブジェクト（`profile`リレーションを含む）をビューに渡します。

##### フォーム送信 (更新処理)

-   **ルート**: `PUT /owner/shops/{shop:slug}/staffs/{staff}`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopStaffController@update`
-   **リクエストクラス**: `App\Http\Requests\Owner\UpdateShopStaffRequest`
-   **処理内容**:
    1.  `UpdateShopStaffRequest`で認可とバリデーションを実行します。
    2.  `shop_staff_profiles`テーブルの情報を`updateOrCreate`で更新・作成します。
    3.  更新後、スタッフ一覧画面 (`/owner/shops/{shop:slug}/staffs`) にリダイレクトし、成功メッセージを表示します。

---

---

### 4.3. メニュー管理

**(今後、このセクションにメニュー管理関連の画面仕様を記述していきます)**

---

### 4.4. 予約管理

**(今後、このセクションに予約管理関連の画面仕様を記述していきます)**

---

### 4.5. 契約情報

**(今後、このセクションに契約情報確認画面の仕様を記述していきます)**

---

### 4.6. ダッシュボード

**(今後、このセクションにダッシュボード画面の仕様を記述していきます)**
