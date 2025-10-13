# 管理者機能仕様書

## 目的

このドキュメントは、予約システム管理者 (Admin) が利用する機能の UI/UX、API エンドポイント、およびデータインタラクションに関する詳細な仕様を定義します。

## 対象ユーザー

-   Admin (予約システム管理者)

---

## 主な画面と機能

本仕様書で定義する主な画面と、その機能概要は以下の通りです。

-   契約申し込み一覧画面(`/admin/contract-applications`)
    -   オーナー予定者からの契約申し込みを一覧で確認し、絞り込み検索を行います。契約新規作成画面へ遷移します。
-   契約申し込み詳細画面(`/admin/contract-applications/{id}`)
    -   オーナー予定者からの契約申し込みの詳細情報を表示し、契約申し込み詳細管理画面へ遷移します。
-   契約申し込み詳細管理画面(`/admin/contract-applications/{id}/edit`)
    -   契約申し込みの詳細情報を確認し、契約申し込みのステータス変更を行います。
-   契約新規作成画面(`/admin/contracts/create`)
    -   オーナー予定者からの契約申し込みの情報から、新規契約を作成するための画面です。
-   契約一覧画面(`/admin/contracts`)
    -   システムに登録されている契約状態を一覧で確認し、絞り込み検索を行います。契約詳細画面へ遷移します。
-   契約詳細画面(`/admin/contracts/{id}`)
    -   特定契約の詳細情報を表示し、契約詳細管理画面へ遷移します。
-   契約詳細管理画面(`/admin/contracts/{id}/edit`)
    -   特定契約の詳細情報を確認し、契約更新、削除を行います。
-   オーナー一覧画面(`/admin/owners`)
    -   システムに登録されている全ユーザーを一覧で確認し、各ユーザーのオーナー管理画面へ遷移します。
-   オーナー詳細画面(`/admin/owners/{public-id}`)
    -   特定ユーザーのオーナー登録状況を表示し、オーナー詳細管理画面へ遷移します。
-   オーナー詳細管理画面(`/admin/owners/{public-id}/edit`)
    -   特定ユーザーのオーナー登録状況を確認し、オーナー登録、削除を行います。

---

## 契約申し込み一覧画面

### 機能概要

オーナー予定者からの契約申し込みを一覧で確認し、各申し込みに対して新規契約作成などのアクションを行うための画面です。
一覧画面は、`docs/phase-2/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された、`<v-data-table-server>`コンポーネントを利用したサーバーサイド処理を標準パターンとして実装します。

### 画面仕様詳細 (`/admin/contract-applications`)

#### 表示項目一覧

| カラム（ラベル） | 表示内容                                                                                        | フィルタ                                  | ソート | 操作                       |
| ---------------- | ----------------------------------------------------------------------------------------------- | ----------------------------------------- | ------ | -------------------------- |
| 申込 ID          | contract_applications.id                                                                        | 可 (インプット)                           | 可     | 空白                       |
| PublicID         | contract_applications.user_id => users.public_id                                                | 可 (インプット)                           | 可     | 空白                       |
| お客様名称       | contract_applications.customer_name                                                             | 可 (インプット)                           | 可     | 空白                       |
| 申込日時         | contract_applications.created_at {ブラウザのタイムゾーンに合わせる 例：yyyy-mm-ddThh:mm:ss JST} | 可 (日付選択、範囲指定)                   | 可     | 空白                       |
| ステータス       | contract_applications.status                                                                    | 可 (セレクト:pending, approved, rejected) | なし   | 空白                       |
| 契約状況         | contracts.application_id => contracts.status (or なし)                                          | 可 (セレクト:active, expired, なし)       | なし   | 空白                       |
| 操作             | ボタン２つ                                                                                      | 不可                                      | なし   | 申し込み詳細　契約を作成　 |

### バックエンド仕様

#### API エンドポイント

-   URL: `GET /admin/api/contract-applications`
-   コントローラ: `Api\Admin\ContractApplicationsController@index`
-   リクエストクラス: `App\Http\Requests\Api\Admin\IndexContractApplicationRequest`
-   クエリパラメータ:
    -   `id` (integer): 申込 ID による完全一致検索。
    -   `public_id` (string): ユーザーの Public ID による完全一致検索。
    -   `customer_name` (string): お客様名称による部分一致検索。
    -   `statuses[]` (array): 申し込みステータスによる絞り込み (`pending`, `approved`, `rejected`)。複数指定可能。
    -   `contract_statuses[]` (array): 契約ステータスによる絞り込み (`active`, `expired`)。`none` を指定すると契約が存在しない申込が対象。複数指定可能。
    -   `created_at_after` (date): 指定日以降の申込日で絞り込み (例: `2025-01-01`)。
    -   `created_at_before` (date): 指定日以前の申込日で絞り込み (例: `2025-12-31`)。
    -   `sort_by` (string): ソート対象カラム。許可される値は `id`, `public_id`, `customer_name`, `created_at`。
    -   `sort_order` (string): ソート方向。許可される値は `asc`, `desc`。
    -   `page` (integer): ページ番号。
    -   `per_page` (integer): 1 ページあたりの表示件数。

### 処理内容

-   `contract_applications`テーブルを主軸に、`with(['user', 'contract'])`を利用してリレーション先の情報を Eager Load します。
-   フィルタやソートの条件に応じて、`users`テーブルや`contracts`テーブルを動的に`LEFT JOIN`します。
-   ページネーションを適用して結果を返却します。

---

## 契約申し込み詳細画面

### 機能概要

契約申し込み一覧画面で選択された、特定の契約申し込みに関する詳細情報を表示します。
この画面は情報の照会を目的としており、表示されるデータはすべて読み取り専用です。
契約申し込み内容の変更は「契約申し込み詳細管理画面」で行います。

### 画面仕様詳細 (`/admin/contract-applications/{id}`)

-   契約申し込み一覧画面へのリンクをページ上部に表示

#### 表示項目一覧

| 項目               | データソース                                         | 備考 |
| :----------------- | :--------------------------------------------------- | :--- |
| 申込 ID            | `contract_applications.id`                           |      |
| PublicID           | `contract_applications.user_id` => `users.public_id` |      |
| お客様名称         | `contract_applications.customer_name`                |      |
| メールアドレス     | `contract_applications.email`                        |      |
| 申し込みステータス | `contract_applications.status`                       |      |
| 申込日時           | `contract_applications.created_at`                   |      |

#### UI 要素

-   ウィンドウタイトル: `契約申し込み詳細`
-   ページタイトル: `契約申し込み詳細`
-   ページタイトルと横並びで「申し込みステータスを編集する」ボタンを配置する。ボタンは右寄せで配置。モバイルビューの場合で、タイトルと重なる場合は、ページタイトル、「申し込みステータスを編集する」ボタンの積み上げ配置
-   「申し込みステータスを編集する」ボタン: `/admin/contract-applications/{id}/edit` へ遷移します。
-   `v-card`コンポーネントを利用し、「契約申し込み情報」セクションを明確に分離して表示

### バックエンド仕様

#### データ受け渡し

-   コントローラは、ルートモデルバインディングによって特定された`ContractApplication`インスタンスを受け取ります。
-   必要に応じて、以下のテーブルから表示用情報を取得

    -   `contract_applications`: 契約申し込み情報
    -   `users`: 契約申し込みに紐づくユーザーの PublicID

-   渡す方法は`docs/phase-2/ARCHITECTURE.md` の方法に従う。

#### API エンドポイント

    契約申し込み詳細画面 API エンドポイントは提供しない。

---

## 契約申し込み詳細管理画面

### 機能概要

管理者が特定の契約申し込みのステータスを更新するための画面です。画面表示時には現在の契約申し込み情報がフォームに設定されており、管理者はこれを編集して更新します。

### 画面仕様詳細 (`/admin/contract-applications/{id}/edit`)

-   契約申し込み一覧画面へのリンクをページ上部に表示

#### 表示項目一覧（読み取り専用）

| 項目           | データソース                                         | 備考 |
| :------------- | :--------------------------------------------------- | :--- |
| 申込 ID        | `contract_applications.id`                           |      |
| PublicID       | `contract_applications.user_id` => `users.public_id` |      |
| お客様名称     | `contract_applications.customer_name`                |      |
| メールアドレス | `contract_applications.email`                        |      |
| 申込日時       | `contract_applications.created_at`                   |      |

#### UI 要素（フォーム要素）

| 項目               | 型/UI    | 必須 | バリデーション/備考                                |
| :----------------- | :------- | :--- | :------------------------------------------------- |
| 申し込みステータス | `select` | ○    | `pending`, `approved`, `rejected` から選択         |
| 更新ボタン         | `button` | -    | フォームの内容を `update` アクションに送信します。 |

### バックエンド仕様

#### データ受け渡し

-   コントローラは、ルートモデルバインディングによって特定された`ContractApplication`インスタンスを受け取ります。
-   `ContractApplication`モデルから、関連する`user`の情報を Eager Load します。
-   取得した`ContractApplication`オブジェクトをビューに渡します。
-   CSRF トークンを blade → Vue に渡す。
-   渡す方法は`docs/phase-2/ARCHITECTURE.md` の方法に従う。

#### フォーム送信（更新）

-   送信先: `PUT /admin/contract-applications/{application}`
-   コントローラ: `App\Http\Controllers\Admin\ContractApplicationsController@update`
-   リクエストクラス: `App\Http\Requests\Admin\UpdateContractApplicationRequest`

#### バリデーション（更新）

-   バックエンド (FormRequest: UpdateContractApplicationRequest):

    -   `status`: `required`, `in:pending,approved,rejected`

#### 処理内容（更新）

1.  バリデーション成功時:

    -   `contract_applications` テーブルの `status` を更新
    -   成功後、契約申し込み詳細画面 (`/admin/contract-applications/{id}`) へリダイレクトし、成功メッセージを表示する

2.  バリデーション失敗時:

    -   画面遷移せず、エラーメッセージを表示。

#### API エンドポイント

    契約申し込み詳細管理画面に関する API エンドポイントは提供しない。

---

## 契約新規作成画面

### 機能概要

契約申し込み一覧画面で選択された「契約申し込み」情報に基づき、管理者が正式な「契約」情報を作成するための画面です。
契約情報（契約名、店舗上限数、契約期間など）を入力し、保存します。保存が成功すると、対象のユーザーは「オーナー」としてシステム上で認識されます。
契約申し込み一覧画面の「契約を作成」ボタンから遷移します。URL のクエリパラメータ `application_id` を必須とします。

### 画面仕様詳細 (`/admin/contracts/create?application_id={id}`)

-   契約一覧画面へのリンクをページ上部に表示

#### 表示項目一覧（読み取り専用）

契約申し込み情報を表示します。

| 項目               | データソース                                |
| :----------------- | :------------------------------------------ |
| 申込 ID            | `contract_applications.id`                  |
| PublicID           | `contract_applications.user` -> `public_id` |
| お客様名称         | `contract_applications.customer_name`       |
| メールアドレス     | `contract_applications.email`               |
| 申し込みステータス | `contract_applications.status`              |

#### UI 要素（フォーム要素）

| 項目                     | 型/UI    | 必須 | デフォルト値   | バリデーション/備考                            |
| :----------------------- | :------- | :--- | :------------- | :--------------------------------------------- |
| `application_id`         | `hidden` | ○    | (URL から取得) | `exists:contract_applications,id`              |
| `user_id`                | `hidden` | ○    | (申込情報から) | `exists:users,id`                              |
| 契約名                   | `text`   | ○    | なし           | `string`, `max:255` 管理者が識別するための名称 |
| 店舗上限数               | `number` | ○    | `1`            | `integer`, `min:1,max:100`                     |
| 契約ステータス           | `select` | ○    | `active`       | `active` または `expired` から選択             |
| 契約開始日               | `date`   | ○    | (当日)         | `date`                                         |
| 契約終了日               | `date`   | ○    | (1 年後)       | `date`, `after_or_equal:契約開始日`            |
| 「契約を作成する」ボタン | `button` | -    | -              | 申し込みステータスが`pending`の場合のみ有効化  |

### バックエンド仕様

#### データ受け渡し

-   コントローラ（`App\Http\Controllers\Admin\ContractsController@create`）でリクエストから `application_id` を取得
-   application_id`をキーに`ContractApplication` モデル（`user` リレーションを含む）から表示用情報を取得。見つからない場合は 404 エラーする。
-   CSRF トークンを blade → Vue に渡す。
-   渡す方法は`docs/phase-2/ARCHITECTURE.md` の方法に従う。

#### フォーム送信

-   送信先: `POST /admin/contracts`
-   送信項目:
-   コントローラ: `App\Http\Controllers\Admin\ContractsController@store`
-   リクエストクラス: `App\Http\Http\Requests\Admin\StoreContractRequest`

#### バリデーション

-   フロントエンド:

    -   必須項目チェック

-   バックエンド (FormRequest: StoreContractApplicationRequest):

    -   必須項目チェック
    -   `application_id`存在チェック
    -   `user_id`存在チェック
    -   `contract_applications`テーブルの`id` => `user_id` と、POST データの一致確認

#### 処理内容

このフォーム送信、成功で、`contracts`テーブルや`owners`テーブルへのレコード作成を行う。

-   `contracts` テーブルの制約：現在はオーナーが複数契約を持つことが可能。もし一つに制限するなら、テーブルの制約をいれるなど検討が必要。
-   `owners` テーブルの制約：`owners`テーブルの`user_id` にユニーク制約をつけている。現在、複数契約時は既存のテーブルの内容を更新する。

1. バリデーション成功時:

    - トランザクションを開始
    - `contracts` テーブルに登録
    - `owners` テーブルに登録、または更新
    - `contract_applications` テーブルの対象レコードの `status` を `approved` に更新
    - トランザクションをコミット
    - 成功後、契約一覧画面 (`/admin/contracts`) へリダイレクトし、成功メッセージを表示する

2. バリデーション失敗時:

    - 画面遷移せず、エラーメッセージを表示。

#### API エンドポイント

    契約申し込みフォームに関する API エンドポイントは提供しない。

---

## 契約一覧画面

### 機能概要

システムに登録されている全契約を一覧で確認し、各契約に対して詳細確認などのアクションを行うための画面です。
一覧画面は、`docs/phase-2/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された、`<v-data-table-server>`コンポーネントを利用したサーバーサイド処理を標準パターンとして実装します。

### 画面仕様詳細 (`/admin/contracts`)

#### 表示項目一覧

| カラム（ラベル） | 表示内容                                                               | フィルタ                       | ソート | 操作       |
| ---------------- | ---------------------------------------------------------------------- | ------------------------------ | ------ | ---------- |
| 契約 ID          | contracts.id                                                           | 不可                           | なし   | 空白       |
| 契約名           | contracts.name                                                         | 可 (インプット)                | 可     | 空白       |
| PublicID         | contracts.user_id => users.public_id                                   | 可 (インプット)                | 可     | 空白       |
| 契約開始日       | contracts.start_date {ブラウザのタイムゾーンに合わせる 例：yyyy-mm-dd} | 可 (日付選択、範囲指定)        | 可     | 空白       |
| 契約終了日       | contracts.end_date {ブラウザのタイムゾーンに合わせる 例：yyyy-mm-dd}   | 可 (日付選択、範囲指定)        | 可     | 空白       |
| ステータス       | contracts.status                                                       | 可 (セレクト: active, expired) | なし   | 空白       |
| 操作             | 「契約の詳細」ボタン                                                   | 不可                           | なし   | 契約の詳細 |

### バックエンド仕様

#### API エンドポイント

-   URL: `GET /admin/api/contracts`
-   コントローラ: `Api\Admin\ContractsController@index`
-   リクエストクラス: `App\Http\Requests\Api\Admin\IndexContractsRequest`
-   クエリパラメータ:
    -   `name` (string): 契約名による部分一致検索。
    -   `public_id` (string): ユーザーの Public ID による完全一致検索。
    -   `statuses[]` (array): 契約ステータスによる絞り込み (`active`, `expired`)。複数指定可能。
    -   `start_date_after` (date): 指定日以降の契約開始日で絞り込み (例: `2025-01-01`)。
    -   `start_date_before` (date): 指定日以前の契約開始日で絞り込み (例: `2025-12-31`)。
    -   `end_date_after` (date): 指定日以降の契約終了日で絞り込み (例: `2025-01-01`)。
    -   `end_date_before` (date): 指定日以前の契約終了日で絞り込み (例: `2025-12-31`)。
    -   `sort_by` (string): ソート対象カラム。許可される値は `name`, `public_id`, `start_date`, `end_date`。
    -   `sort_order` (string): ソート方向。許可される値は `asc`, `desc`。
    -   `page` (integer): ページ番号。
    -   `per_page` (integer): 1 ページあたりの表示件数。

#### 処理内容

-   `contracts`テーブルを主軸に、`with(['user'])`を利用してリレーション先の情報を Eager Load します。
-   リクエストされたフィルタ条件に基づき、`where`句や`whereHas`句を使って動的にクエリを構築します。
-   リクエストされたソート条件に基づき、`orderBy`句を適用します。
-   ページネーションを適用して結果を返却します。
-   「契約の詳細」ボタンを押すと、対象の契約詳細画面に遷移します。

---

### 契約詳細画面

### 機能概要

契約一覧画面で選択された、特定の契約に関する詳細情報を表示します。
この画面は情報の照会を目的としており、表示されるデータはすべて読み取り専用です。
契約内容の変更は「契約詳細管理画面」で行います。

### 画面仕様詳細 (`/admin/contracts/{id}`)

-   契約一覧画面へのリンクをページ上部に表示

#### 表示項目一覧

##### 契約基本情報

| 項目           | データソース                             | 備考 |
| :------------- | :--------------------------------------- | :--- |
| 契約 ID        | `contracts.id`                           |      |
| 契約名         | `contracts.name`                         |      |
| PublicID       | `contracts.user_id` => `users.public_id` |      |
| 店舗上限数     | `contracts.max_shops`                    |      |
| 契約ステータス | `contracts.status`                       |      |
| 契約開始日     | `contracts.start_date`                   |      |
| 契約終了日     | `contracts.end_date`                     |      |

##### 契約申し込み情報

この契約が、契約申し込みから作成された場合に表示します。

| 項目                 | データソース                                              | 備考 |
| :------------------- | :-------------------------------------------------------- | :--- |
| 申込 ID              | `contracts.application_id`                                |      |
| お客様名称           | `contract_applications.application_id` -> `customer_name` |      |
| お客様メールアドレス | `contract_applications.application_id` -> `email`         |      |
| 申込日時             | `contract_applications.application_id` -> `created_at`    |      |

#### UI 要素

-   ウィンドウタイトル: `契約詳細`
-   ページタイトル: `契約詳細`
-   ページタイトルと横並びで「契約を編集する」ボタンを配置する。ボタンは右寄せで配置。モバイルビューの場合で、タイトルと重なる場合は、ページタイトル、「契約を編集する」ボタンの積み上げ配置
-   「契約を編集する」ボタン: `/admin/contracts/{id}/edit` へ遷移します。
-   `v-card`コンポーネントを複数利用し、「契約基本情報」「契約申し込み情報」の各セクションを明確に分離して表示

### バックエンド仕様

#### データ受け渡し

-   コントローラは、ルートモデルバインディングによって特定された`Contract`インスタンスを受け取ります。
-   必要に応じて、以下のテーブルから表示用情報を取得

    -   `contracts`: 契約情報
    -   `users`: 契約に紐づくユーザーの PublicID
    -   `contract_applications`: 契約申し込み情報

-   渡す方法は`docs/phase-2/ARCHITECTURE.md` の方法に従う。

#### API エンドポイント

    契約詳細画面 API エンドポイントは提供しない。

---

### 契約詳細管理画面

### 機能概要

管理者が特定の契約内容（契約名、契約期間、ステータスなど）を更新、または契約自体を削除するための画面です。画面表示時には現在の契約情報がフォームに設定されており、管理者はこれを編集して更新します。

### 画面仕様詳細 (`/admin/contracts/{id}/edit`)

-   契約一覧画面へのリンクをページ上部に表示

##### 表示項目一覧（読み取り専用）

| 項目                 | データソース                                              | 備考                                                        |
| :------------------- | :-------------------------------------------------------- | :---------------------------------------------------------- |
| 契約 ID              | `contracts.id`                                            |                                                             |
| PublicID             | `contracts.user_id` => `users.public_id`                  |                                                             |
| 申込 ID              | `contracts.application_id`                                | `application_id` がある場合、なければ項目だけ、値はブランク |
| お客様名称           | `contract_applications.application_id` -> `customer_name` | `application_id` がある場合、なければ項目だけ、値はブランク |
| お客様メールアドレス | `contract_applications.application_id` -> `email`         | `application_id` がある場合、なければ項目だけ、値はブランク |
| 申込日時             | `contract_applications.application_id` -> `created_at`    | `application_id` がある場合、なければ項目だけ、値はブランク |

##### UI 要素（フォーム要素）

| 項目           | 型/UI    | 必須 | バリデーション/備考                                    |
| :------------- | :------- | :--- | :----------------------------------------------------- |
| 契約名         | `text`   | ○    | `string`, `max:255`                                    |
| 店舗上限数     | `number` | ○    | `integer`, `min:1,max:100`                             |
| 契約ステータス | `select` | ○    | `active` または `expired` から選択                     |
| 契約開始日     | `date`   | ○    | `date`                                                 |
| 契約終了日     | `date`   | ○    | `date`, `after_or_equal:契約開始日`                    |
| 更新ボタン     | `button` | -    | フォームの内容を `update` アクションに送信します。     |
| 削除ボタン     | `button` | -    | 契約を削除するための確認ダイアログを開きます。（後述） |

##### 削除確認ダイアログ

-   「削除」ボタンをクリックすると、モーダルダイアログが表示されます。
-   ダイアログには「この操作は元に戻せません。契約を削除するには、契約名を入力してください」といった警告メッセージを表示します。また、「契約申し込み情報」は新規契約作成時にのみ設定できます。「契約申し込み情報」を設定するには、契約を「削除」の後に、「新規作成」を行ってください。といった内容も注意事項として表示します。
-   ユーザーが契約名を入力するテキストフィールドを設置し、入力された文字列が現在の契約名と一致した場合のみ、「削除を実行」ボタンが有効になります。

### バックエンド仕様

#### データ受け渡し

-   コントローラは、ルートモデルバインディングによって特定された`Contract`インスタンスを受け取ります。
-   `Contract`モデルから、関連する`user`と`application`の情報を Eager Load します。
-   取得した`Contract`オブジェクトをビューに渡します。
-   CSRF トークンを blade → Vue に渡す。
-   渡す方法は`docs/phase-2/ARCHITECTURE.md` の方法に従う。

#### フォーム送信（更新）

-   送信先: `PUT /admin/contracts/{contract}`
-   送信項目:
-   コントローラ: `App\Http\Controllers\Admin\ContractsController@update`
-   リクエストクラス: `App\Http\Http\Requests\Admin\UpdateContractRequest`

#### バリデーション（更新）

-   フロントエンド:

    -   必須項目チェック

-   バックエンド (FormRequest: UpdateContractRequest):

    -   必須項目チェック
    -   型チェック
    -   各項目の制限（max:255, min:1, max:100 など）チェック
    -   日付の前後関係チェック（契約開始日 <= 契約終了日）
    -   `id`存在チェック

#### 処理内容（更新）

このフォーム送信、成功で、対象の契約情報を更新します。
ただし、更新できる項目は以下の項目のみに制限します。

-   `name`
-   `max_shops`
-   `status`
-   `start_date`
-   `end_date`
    つまり、`contracts`の`user_id`や`application_id`は更新しません。

1. バリデーション成功時:

    - `contracts` テーブルの更新
    - 成功後、契約詳細画面 (`/admin/contracts/{id}`) へリダイレクトし、成功メッセージを表示する

2. バリデーション失敗時:

    - 画面遷移せず、エラーメッセージを表示。

#### フォーム送信（削除）

Laravel のフォームメソッドフィールドを利用して、`DELETE` メソッドで送信します。
ただし、blade 側ではなく、Vue コンポーネント側で実装します。

-   送信先: `DELETE /admin/contracts/{contract}`
-   コントローラ: `App\Http\Controllers\Admin\ContractsController@destroy`
-   リクエストクラス: `App\Http\Http\Requests\Admin\DeleteContractRequest`

#### バリデーション（削除）

-   フロントエンド:

    -   特になし

-   バックエンド (FormRequest: DeleteContractRequest):

    -   Laravel の ルートモデルバインディング (Route Model Binding) を利用して、`contract` パラメータから `Contract` モデルを自動的に解決します。

#### 処理内容（削除）

このフォーム送信、成功で、対象の契約情報を削除します。

1. バリデーション成功時:

    - `contracts` テーブルの対象レコードを削除
    - 成功後、契約一覧画面 (`/admin/contracts`) へリダイレクトし、成功メッセージを表示する

2. バリデーション失敗時:

    - 画面遷移せず、エラーメッセージを表示。

#### API エンドポイント

    契約詳細画面管理に関する API エンドポイントは提供しない。

---

### オーナー一覧画面

### 機能概要

システムに登録されている全オーナーを一覧で確認し、各オーナーの詳細管理画面へ遷移するための画面です。
一覧画面は、`docs/phase-2/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された、`<v-data-table-server>`コンポーネントを利用したサーバーサイド処理を標準パターンとして実装します。

### 画面仕様詳細 (`/admin/owners`)

#### 表示項目一覧

| カラム（ラベル） | 表示内容                                                            | フィルタ                | ソート | 操作       |
| ---------------- | ------------------------------------------------------------------- | ----------------------- | ------ | ---------- |
| PublicID         | owners.user_id => users.public_id                                   | 可 (インプット)         | 可     | 空白       |
| オーナー名       | owners.name                                                         | 可 (インプット)         | 可     | 空白       |
| 登録日時         | owners.created_at {ブラウザのタイムゾーンに合わせる 例：yyyy-mm-dd} | 可 (日付選択、範囲指定) | 可     | 空白       |
| 契約数           | owners.user_id に紐づく contracts の数                              | 可 (数値範囲)           | 可     | 空白       |
| 操作             | 「詳細」ボタン                                                      | 不可                    | なし   | 詳細の表示 |

### バックエンド仕様

#### API エンドポイント

-   URL: `GET /admin/api/owners`
-   コントローラ: `Api\Admin\OwnersController@index`
-   リクエストクラス: `App\Http\Requests\Api\Admin\IndexOwnersRequest`
-   クエリパラメータ:
    -   `public_id` (string): ユーザーの Public ID による部分一致検索。
    -   `name` (string): オーナー名による部分一致検索。
    -   `created_at_after` (date): 指定日以降の登録日で絞り込み (例: `2025-01-01`)。
    -   `created_at_before` (date): 指定日以前の登録日で絞り込み (例: `2025-12-31`)。
    -   `contracts_count_min` (integer): 契約数が指定値以上のオーナーを絞り込み。
    -   `contracts_count_max` (integer): 契約数が指定値以下のオーナーを絞り込み。
    -   `sort_by` (string): ソート対象カラム。許可される値は `public_id`, `name`, `created_at`, `contracts_count`。
    -   `sort_order` (string): ソート方向。許可される値は `asc`, `desc`。
    -   `page` (integer): ページ番号。
    -   `per_page` (integer): 1 ページあたりの表示件数。

#### 処理内容

-   `Owner`モデルを主軸に、`with(['user'])` と `withCount(['contracts'])` を利用してリレーション先の情報を Eager Load します。
-   リクエストされたフィルタ条件に基づき、`where`句, `whereHas`句, `has`句などを使って動的にクエリを構築します。
-   リクエストされたソート条件に基づき、`orderBy`句を適用します。`public_id`でのソートはサブクエリを用いて実現します。
-   ページネーションを適用して結果を返却します。
-   「詳細」ボタンを押すと、対象オーナーの詳細画面 (`/admin/owners/{public_id}`) に遷移します。

---

### オーナー詳細画面

### 機能概要

オーナー一覧画面で選択された、特定のオーナーに関する詳細情報を表示します。
この画面は情報の照会を目的としており、表示されるデータはすべて読み取り専用です。
オーナー情報の変更は「オーナー詳細管理画面」で行います。

### 画面仕様詳細 (`/admin/owners/{public_id}`)

-   オーナー一覧画面へのリンク (`/admin/owners`) をページ上部に表示します。

#### UI 要素

-   ウィンドウタイトル: `オーナー詳細`
-   ページタイトル: `オーナー詳細`
-   ページタイトルと横並びで「オーナー情報を編集する」ボタンを配置します。ボタンは `/admin/owners/{public_id}/edit` へ遷移します。

#### 表示セクション

`v-card` コンポーネント（または同等の Blade コンポーネント）を複数利用し、以下の各セクションを明確に分離して表示します。

##### 1. オーナー基本情報

| 項目       | データソース            | 備考 |
| :--------- | :---------------------- | :--- |
| Public ID  | `owners.user.public_id` |      |
| オーナー名 | `owners.name`           |      |
| 登録日時   | `owners.created_at`     |      |

##### 2. 契約一覧

このオーナーに紐づく契約情報をテーブル形式で表示します。

| カラム（ラベル） | 表示内容                                      | 操作                                                |
| :--------------- | :-------------------------------------------- | :-------------------------------------------------- |
| 契約 ID          | `contracts.id`                                |                                                     |
| 契約名           | `contracts.name`                              |                                                     |
| ステータス       | `contracts.status`                            |                                                     |
| 契約期間         | `contracts.start_date` ~ `contracts.end_date` |                                                     |
| 操作             | 「詳細」ボタン                                | 対象の契約詳細画面 (`/admin/contracts/{id}`) へ遷移 |

##### 3. 店舗一覧

このオーナーが所有する店舗情報をテーブル形式で表示します。

| カラム（ラベル） | 表示内容     | 操作 |
| :--------------- | :----------- | :--- |
| 店舗 ID          | `shops.id`   |      |
| 店舗 Slug        | `shops.slug` |      |
| 店舗名           | `shops.name` |      |

### バックエンド仕様

#### データ受け渡し

-   コントローラ (`App\Http\Controllers\Admin\OwnersController@show`) は、ルートキーとして受け取った `public_id` をもとに、対象の `User` を検索し、それに紐づく `Owner` を取得します。
-   `Owner` モデルから、関連する `user`, `contracts`, `shops` の情報を Eager Load してビューに渡します。
-   対象のオーナーが見つからない場合は 404 エラーとします。

#### API エンドポイント

この画面はサーバーサイドで描画が完結するため、専用の API エンドポイントは提供しません。

---

### オーナー詳細管理画面

### 機能概要

管理者が特定のオーナー情報（オーナー名）を更新、またはオーナーの役割自体を削除（取消）するための画面です。画面表示時には現在のオーナー情報がフォームに設定されており、管理者はこれを編集して更新します。

### 画面仕様詳細 (`/admin/owners/{public-id}/edit`)

-   オーナー詳細画面へのリンク (`/admin/owners/{public_id}`) をページ上部に表示します。

##### 表示項目一覧（読み取り専用）

| 項目      | データソース            | 備考 |
| :-------- | :---------------------- | :--- |
| Public ID | `owners.user.public_id` |      |
| 登録日時  | `owners.created_at`     |      |

##### UI 要素（フォーム要素）

| 項目       | 型/UI    | 必須 | バリデーション/備考                                        |
| :--------- | :------- | :--- | :--------------------------------------------------------- |
| オーナー名 | `text`   | ○    | `string`, `max:255`                                        |
| 更新ボタン | `button` | -    | フォームの内容を `update` アクションに送信します。         |
| 削除ボタン | `button` | -    | オーナーを削除するための確認ダイアログを開きます。（後述） |

##### 削除確認ダイアログ

-   「削除」ボタンをクリックすると、モーダルダイアログが表示されます。
-   ダイアログには「この操作により、ユーザーはオーナーとしての権限を失います。ユーザーアカウント自体は削除されません。操作を元に戻すことはできません。オーナーを削除するには、オーナー名を入力してください」といった警告メッセージを表示します。
-   ユーザーがオーナー名を入力するテキストフィールドを設置し、入力された文字列が現在のオーナー名と一致した場合のみ、「削除を実行」ボタンが有効になります。

#### データ受け渡し (`edit` メソッド)

-   コントローラ (`App\Http\Controllers\Admin\OwnersController@edit`) は、ルートキーとして受け取った `public_id` をもとに、対象の `User` を検索し、それに紐づく `Owner` を取得します。
-   取得した `Owner` オブジェクトをビューに渡します。
-   対象のオーナーが見つからない場合は 404 エラーとします。

#### フォーム送信（更新）

-   送信先: `PUT /admin/owners/{public_id}`
-   コントローラ: `App\Http\Controllers\Admin\OwnersController@update`
-   リクエストクラス: `App\Http\Requests\Admin\UpdateOwnerRequest`

#### バリデーション（更新）

-   バックエンド (`UpdateOwnerRequest`):
    -   `name`: `required`, `string`, `max:255`

#### 処理内容（更新）

1.  バリデーション成功時:
    -   対象の `Owner` レコードの `name` を更新します。
    -   成功後、オーナー詳細画面 (`/admin/owners/{public_id}`) へリダイレクトし、成功メッセージを表示します。
2.  バリデーション失敗時:
    -   画面遷移せず、エラーメッセージを表示します。

#### フォーム送信（削除）

-   送信先: `DELETE /admin/owners/{public_id}`
-   コントローラ: `App\Http\Controllers\Admin\OwnersController@destroy`
-   リクエストクラス: `App\Http\Requests\Admin\DeleteOwnerRequest` (認可のみ)

#### バリデーション（削除）

-   バックエンド:
    オーナーに、契約、店舗が紐づいている場合は削除できないようにします。

#### 処理内容（削除）

1.  認可成功時:
    1.1. バリデーション成功時:

    -   対象の `Owner` レコードを削除します。関連する `User` レコードは削除しません。
    -   成功後、オーナー一覧画面 (`/admin/owners`) へリダイレクトし、成功メッセージを表示します。

    1.2. バリデーション失敗時:

    -   画面遷移せず、エラーメッセージを表示します。

2.  認可失敗時:
    -   403 Forbidden エラーを表示します。

#### API エンドポイント

この画面はサーバーサイドで描画が完結するため、専用の API エンドポイントは提供しません。

---
