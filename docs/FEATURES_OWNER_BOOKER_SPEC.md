# オーナー向け予約者管理機能仕様書

## 1. 目的

店舗オーナー (Owner) が、自店舗の顧客情報（予約者）を一覧で確認し、検索、登録、編集を行うための機能仕様を定義します。

---

## 2. 機能・画面仕様詳細

### 予約者一覧画面

#### 機能概要

オーナーが店舗の顧客情報を一覧表示し、管理するための基本画面です。ここから各予約者の詳細確認や新規登録へ進みます。
一覧画面は、`docs/phase-3/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された標準パターンで実装します。

#### 画面仕様詳細 (`/owner/shops/{shop:slug}/bookers`)

##### 表示項目一覧

リンクセクション：

-   店舗詳細画面へのリンクをページ上部に表示します。

店舗ヘッダー：

-   `店舗：{店舗名} (店舗 ID: {店舗 ID})` の共通ヘッダーを表示します。

予約者一覧セクション：

-   セクションタイトル: `予約者一覧`
-   **「予約者を新規登録する」ボタン**:
    -   セクションタイトルと横並びで配置します（ボタンは右寄せ）。
    -   クリックすると予約者登録画面 (`/owner/shops/{shop:slug}/bookers/create`) へ遷移します。

| カラム（ラベル）         | 表示内容                            | フィルタ          | ソート | 操作           |
| :----------------------- | :---------------------------------- | :---------------- | :----- | :------------- |
| **会員番号**             | `shop_bookers.number`               | 可 (テキスト入力) | 可     |                |
| **名前**                 | `shop_bookers.name`                 | 可 (テキスト入力) | 可     |                |
| **よみかた**             | `shop_bookers_crm.name_kana`        | 可 (テキスト入力) | 可     |                |
| **連絡先メールアドレス** | `shop_bookers.contact_email`        | 可 (テキスト入力) | 可     |                |
| **連絡先電話番号**       | `shop_bookers.contact_phone`        | 可 (テキスト入力) | 不可   |                |
| **最終予約日時**         | `shop_bookers_crm.last_booking_at`      | 可 (日付範囲)     | 可     |                |
| **予約回数**             | `shop_bookers_crm.booking_count`        | 可 (数値範囲)     | 可     |                |
| **操作**                 | 「編集」ボタン                          | 不可              | なし   | 編集画面へ遷移 |

#### バックエンド仕様

##### ページ表示時のデータ（Web コントローラから渡す情報）

`Owner\ShopBookerController@index` は、Vue コンポーネントをマウントする Blade ビューを返します。その際、以下の情報を`props`として Vue コンポーネントに渡します。

| データ名 | 型       | 説明             |
| :------- | :------- | :--------------- |
| `shop`   | `object` | 現在の店舗情報。 |

##### API エンドポイント

-   **URL**: `GET /owner/api/shops/{shop:slug}/bookers`
-   **コントローラ**: `Api\Owner\ShopBookerController@index`
-   **リクエストクラス**: `App\Http\Requests\Api\Owner\IndexShopBookersRequest`
- **クエリパラメータ**:
    -   `number` (string): 会員番号による完全一致検索。
    -   `name` (string): 名前による部分一致検索。
    -   `name_kana` (string): よみかたによる部分一致検索。
    -   `contact_email` (string): メールアドレスによる部分一致検索。
    -   `contact_phone` (string): 電話番号による部分一致検索。
    -   `sort_by` (string): ソート対象カラム。(`number`, `name`, `name_kana`, `contact_email`, `last_booking_at`, `booking_count` など)
    -   `sort_order` (string): ソート方向 (`asc` or `desc`)。
    -   `page` (integer): ページ番号。
    -   `per_page` (integer): 1 ページあたりの表示件数。

##### 処理内容

-   `ShopBookerPolicy@viewAny` により、認可されたデータのみが返却されることを保証します。
-   ログインしているオーナーの、URL で指定された店舗に紐づく `shop_bookers` テーブルのレコードを返却します。
-   受け取ったクエリパラメータに基づき、動的なフィルタリング、ソーティングを適用します。
-   ページネーションを適用して結果を返却します。
-   「編集」ボタンを押すと、対象予約者の編集画面 (`/owner/shops/{shop}/bookers/{booker}/edit`) に遷移します。

---

### 予約者登録画面

#### 機能概要

オーナーが、店舗の顧客（予約者）を新規に手動で登録するための画面です。

#### 画面仕様詳細 (`/owner/shops/{shop:slug}/bookers/create`)

-   **ウィンドウタイトル**: `予約者新規登録`
-   **ページコンポーネント**: `owner/shops/bookers/Create.vue`

##### 表示項目一覧

**リンクセクション：**

-   「予約者一覧に戻る」へのリンクをページ上部に表示

**店舗ヘッダー：**

-   `店舗：{店舗名}` の共通ヘッダーを表示します。

**予約者新規登録セクション：**

-   **フォーム**:
    -   フォーム全体は `v-card` で囲みます。
    -   カードタイトルは「予約者新規登録」とします。
    -   カードの最下部に「登録する」ボタンを配置します。

##### 予約者・店舗共通情報
| ラベル                   | DB カラム                         | UI                   | 備考                                         |
| :----------------------- | :-------------------------------- | :------------------- | :------------------------------------------- |
| **名前**                 | `shop_bookers.name`               | テキスト入力         | 必須。お客様の呼び名などを入力します。       |
| **連絡先メールアドレス** | `shop_bookers.contact_email`      | テキスト入力 (email) | 予約通知などに使用するメールアドレス。 |
| **連絡先電話番号**       | `shop_bookers.contact_phone`      | テキスト入力 (tel)   | お客様の電話番号。                     |
| **予約者からのメモ**     | `shop_bookers.note_from_booker`   | テキストエリア       | 任意。予約者が入力するメモ。                 |

##### 店舗管理用の情報
| ラベル         | DB カラム                    | UI                 | 備考                                       |
| :------------- | :--------------------------- | :----------------- | :----------------------------------------- |
| **よみかた**   | `shop_bookers_crm.name_kana` | テキスト入力       | 任意。                     |
| **店舗側メモ** | `shop_bookers_crm.shop_memo` | テキストエリア     | 任意。お客様に関する店舗側で管理するメモ。 |
| **最終予約日時** | `shop_bookers_crm.last_booking_at` | 日付時刻ピッカー | 任意。手動で最終予約日時を設定する場合に入力。 |
| **予約回数**   | `shop_bookers_crm.booking_count`   | 数値入力           | 任意。手動で予約回数を設定する場合に入力。     |

---

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/bookers/create`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopBookerController@create`
-   **処理内容**:
    -   `ShopBookerPolicy@create` を使用し、認可チェックを行います。
    -   成功した場合は、Vue コンポーネントをマウントする `owner.bookers.create` ビューを返します。
    -   ビューには `shop` オブジェクトを渡します。

##### フォーム送信 (登録処理)

-   **ルート**: `POST /owner/shops/{shop:slug}/bookers`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopBookerController@store`
-   **リクエストクラス**: `App\Http\Requests\Owner\StoreShopBookerRequest`

##### バリデーション (`StoreShopBookerRequest`)

-   **認可**: `authorize`メソッド内で`ShopBookerPolicy@create`を呼び出します。
- **ルール**:
    -   `name`: `required`, `string`, `max:255`
    -   `name_kana`: `nullable`, `string`, `max:255`
    -   `contact_email`: `required`, `email`, `max:255`, `unique:shop_bookers,contact_email,NULL,id,shop_id,{shop_id}` (同一店舗内での重複を許さない)
    -   `contact_phone`: `required`, `string`, `max:20`
    -   `note_from_booker`: `nullable`, `string`
    -   `shop_memo`: `nullable`, `string`
    -   `last_booking_at`: `nullable`, `date`
    -   `booking_count`: `nullable`, `integer`, `min:0`

##### 処理内容

1.  `StoreShopBookerRequest` で認可とバリデーションを実行します。
2.  バリデーションが成功した場合、`shop_bookers` テーブルに新しいレコードを作成します。`shop_id` には現在の店舗の ID を設定します。
3.  登録後、予約者一覧画面 (`/owner/shops/{shop:slug}/bookers`) にリダイレクトし、「予約者を登録しました」という成功メッセージを表示します。

---

### 予約者編集画面

#### 機能概要

オーナーが、既存の予約者の情報を編集するための画面です。

#### 画面仕様詳細 (`/owner/shops/{shop:slug}/bookers/{booker}/edit`)

-   **ウィンドウタイトル**: `予約者編集: {予約者名}`
-   **ページコンポーネント**: `owner/shops/bookers/Edit.vue`

##### 表示項目一覧

**リンクセクション：**

-   「予約者一覧に戻る」へのリンクをページ上部に表示

**店舗ヘッダー：**

-   `店舗：{店舗名}` の共通ヘッダーを表示します。

**予約者編集セクション：**

-   **フォーム**:
    -   フォーム全体は `<v-card>` で囲みます。
    -   カードタイトルは「予約者編集」とします。
    -   フォームの各項目には、編集対象予約者の既存データが初期表示されます。
    -   カードの最下部に「削除する」ボタンと「更新する」ボタンを配置します。(※削除処理は今回実装しませんが、ボタンは非活性状態で表示)

##### 予約者・店舗共通情報

このセクションの情報は、予約者自身が予約時に入力する項目や、予約者ページのプロフィール画面で編集できる項目です。オーナーも利便性のために編集可能です。

| ラベル                   | DB カラム                       | UI                   | 備考                         |
| :----------------------- | :------------------------------ | :------------------- | :--------------------------- |
| **会員番号**             | `shop_bookers.number`           | テキスト（編集不可） | システムで自動採番される番号 |
| **名前**                 | `shop_bookers.name`             | テキスト入力         | 必須                         |
| **連絡先メールアドレス** | `shop_bookers.contact_email`    | テキスト入力 (email) | 同一店舗内でユニーク         |
| **連絡先電話番号**       | `shop_bookers.contact_phone`    | テキスト入力 (tel)   |                              |
| **予約者からのメモ**     | `shop_bookers.note_from_booker` | テキストエリア       | 予約者が入力するメモ         |

##### 店舗管理用の情報

このセクションの情報は、店舗側の顧客管理のために利用され、予約者には表示されません。



| ラベル         | DB カラム                    | UI                 | 備考                                       |

| :------------- | :--------------------------- | :----------------- | :----------------------------------------- |

| **よみかた**   | `shop_bookers_crm.name_kana` | テキスト入力       |                                            |

| **店舗側メモ** | `shop_bookers_crm.shop_memo` | テキストエリア     |                                            |

| **最終予約日時** | `shop_bookers_crm.last_booking_at` | 日付時刻ピッカー | 任意。手動で最終予約日時を設定する場合に入力。 |

| **予約回数**   | `shop_bookers_crm.booking_count`   | 数値入力           | 任意。手動で予約回数を設定する場合に入力。     |

---

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/bookers/{booker}/edit`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopBookerController@edit`
-   **処理内容**:
    1.  `ShopBookerPolicy@update` を使用し、認可チェックを行います。
    2.  ルートモデルバインディングで編集対象の `ShopBooker` モデルを取得します。
    3.  `shop` と `booker` の情報を `props` として持つ `owner.bookers.edit` ビューを返します。

##### フォーム送信 (更新処理)

-   **ルート**: `PUT /owner/shops/{shop:slug}/bookers/{booker}`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopBookerController@update`
-   **リクエストクラス**: `App\Http\Requests\Owner\UpdateShopBookerRequest`

##### バリデーション (`UpdateShopBookerRequest`)

-   **認可**: `authorize`メソッド内で`ShopBookerPolicy@update`を呼び出します。
- **ルール**:
    -   `name`: `required`, `string`, `max:255`
    -   `name_kana`: `nullable`, `string`, `max:255`
    -   `contact_email`: `required`, `email`, `max:255`, `unique:shop_bookers,contact_email,{booker_id},id,shop_id,{shop_id}` (自身のメールアドレスを除き、同一店舗内での重複を許さない)
    -   `contact_phone`: `required`, `string`, `max:20`
    -   `note_from_booker`: `nullable`, `string`
    -   `shop_memo`: `nullable`, `string`
    -   `last_booking_at`: `nullable`, `date`
    -   `booking_count`: `nullable`, `integer`, `min:0`

##### 処理内容

1.  `UpdateShopBookerRequest` で認可とバリデーションを実行します。
2.  対象の `ShopBooker` モデルの情報を更新します。
3.  更新後、予約者一覧画面へリダイレクトし、「予約者情報を更新しました」という成功メッセージを表示します。
