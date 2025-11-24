### メニュー一覧画面

#### 機能概要

オーナーが店舗で提供するメニューを一覧表示し、管理するための基本画面です。ここから各メニューの詳細設定や新規メニューの登録へ進みます。
一覧画面は、`docs/phase-3/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された標準パターンで実装します。

#### 画面仕様詳細 (`/owner/shops/{shop}/menus`)

##### 表示項目一覧

リンクセクション：

-   店舗詳細画面へのリンクをページ上部に表示

店舗ヘッダー：

-   店舗：{店舗名} (店舗 ID: {店舗 ID})
    これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

メニュー一覧セクション

-   セクションタイトル: `メニュー一覧`
-   **「メニューを新規登録する」ボタン**:
    -   セクションタイトルと横並びで配置します（ボタンは右寄せ）。
    -   クリックするとメニュー登録画面へ遷移します。

| カラム（ラベル）   | 表示内容                                                                                                                                                                                                            | フィルタ              | ソート | 操作           |
| :----------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | :-------------------- | :----- | :------------- |
| メニュー名         | `shop_menus.name`                                                                                                                                                                                                   | 可 (テキスト入力)     | 可     |                |
| 価格               | `shop_menus.price` (円)                                                                                                                                                                                             | 可 (数値範囲)         | 可     |                |
| 所要時間           | `shop_menus.duration` (分)                                                                                                                                                                                          | 可 (数値範囲)         | 可     |                |
| 担当者必須         | `shop_menus.requires_staff_assignment`                                                                                                                                                                              | 可 (セレクトボックス) | 可     |                |
| 担当スタッフ       | このメニューを担当可能なスタッフのニックネームをカンマ区切りで表示                                                                                                                                                  | 可 (セレクトボックス) | 不可   |                |
| 関連オプション     | このメニューに紐づくオプションの名称をカンマ区切りで表示                                                                                                                                                            | 可 (セレクトボックス) | 不可   |                |
| 特別キャンセル期限 | `requires_cancellation_deadline` が `false` の場合、店舗設定（`shops.cancel_deadline_hours`）を「店舗設定（Y 時間前）」のように表示。<br>`true` の場合は `cancellation_deadline_minutes` を「X 分前」のように表示。 | 可 (数値範囲)         | 可     |                |
| 特別予約締切       | `requires_booking_deadline` が `false` の場合、店舗設定（`shops.booking_deadline_hours`）を「店舗設定（Y 時間前）」のように表示。<br>`true` の場合は `booking_deadline_minutes` を「X 分前」のように表示。          | 可 (数値範囲)         | 可     |                |
| 操作               | 「編集」ボタン                                                                                                                                                                                                      | 不可                  | なし   | 編集画面へ遷移 |

#### バックエンド仕様

##### ページ表示時のデータ（Web コントローラから渡す情報）

`Owner\ShopMenuController@index` は、Vue コンポーネントをマウントする Blade ビューを返します。その際、以下の情報を`props`として Vue コンポーネントに渡します。

| データ名 | 型       | 説明             |
| :------- | :------- | :--------------- |
| `shop`   | `object` | 現在の店舗情報。 |

##### API エンドポイント

-   **URL**: `GET /owner/api/shops/{shop}/menus`
-   **コントローラ**: `Api\Owner\ShopMenuController@index`
-   **リクエストクラス**: `App\Http\Requests\Api\Owner\IndexShopMenusRequest`
-   **クエリパラメータ**:
    -   `name` (string): メニュー名による部分一致検索。
    -   `price_from` / `price_to` (integer): 価格による範囲検索。
    -   `duration_from` / `duration_to` (integer): 所要時間による範囲検索。
    -   `requires_staff_assignment` (boolean): `true`または`false`による絞り込み。
    -   `sort_by` (string): ソート対象カラム。
    -   `sort_order` (string): ソート方向 (`asc` or `desc`)。
    -   `page` (integer): ページ番号。
    -   `per_page` (integer): 1 ページあたりの表示件数。

##### 処理内容

-   `ShopMenuPolicy@viewAny` により、認可されたデータのみが返却されることを保証します。
-   ログインしているオーナーの、URL で指定された店舗に紐づく `shop_menus` テーブルのレコードを返却します。
-   受け取ったクエリパラメータに基づき、動的なフィルタリング、ソーティングを適用します。
-   ページネーションを適用して結果を返却します。
-   「編集」ボタンを押すと、対象メニューの編集画面 (`/owner/shops/{shop}/menus/{menu}/edit`) に遷移します。

---

### メニュー登録画面

#### 機能概要

オーナーが、店舗で提供するサービス（メニュー）を新規に登録するための画面です。メニューには価格や所要時間に加え、独自の予約・キャンセルポリシーや、担当可能なスタッフ、選択可能なオプションを設定できます。

#### 画面仕様詳細 (`/owner/shops/{shop}/menus/create`)

-   **ウィンドウタイトル**: `メニュー新規登録`

##### 表示項目一覧

リンクセクション：

-   「メニュー一覧に戻る」へのリンクをページ上部に表示

店舗ヘッダー：

-   店舗：{店舗名} (店舗 ID: {店舗 ID})
    これは、店舗に紐づく画面（店舗詳細、営業日、特別休業日、スタッフ管理、メニュー管理など）の共通ヘッダーとして表示します。

メニュー新規登録セクション：

-   **フォーム**:
    -   フォーム全体は `v-card` で囲みます。
    -   カードタイトルは「メニュー新規登録」とします。
    -   カードの最下部に「登録する」ボタンを配置します。

##### フォーム項目一覧

| ラベル                   | DB カラム                        | UI             | 備考                                                                                            |
| :----------------------- | :------------------------------- | :------------- | :---------------------------------------------------------------------------------------------- |
| **メニュー名**           | `name`                           | テキスト入力   | 必須。お客様に表示されるメニューの正式名称を入力します。                                        |
| **価格**                 | `price`                          | 数値入力       | 必須。メニューの価格を円単位で入力します。                                                      |
| **所要時間**             | `duration`                       | 数値入力       | 必須。サービスの所要時間を分単位で入力します。                                                  |
| **メニューの説明**       | `description`                    | テキストエリア | 任意。お客様に表示されるメニューの詳細な説明を入力します。                                      |
| **担当者の割り当て**     | `requires_staff_assignment`      | スイッチ       | このメニューの予約時に、担当スタッフの選択を必須にするかどうかを設定します。                    |
| **特別なキャンセル期限** | `requires_cancellation_deadline` | スイッチ       | 店舗の基本設定とは異なるキャンセル期限を設定する場合にオンにします。                            |
| **キャンセル期限**       | `cancellation_deadline_minutes`  | 数値入力       | (上記スイッチが ON の場合に表示) 予約の何分前までお客様によるキャンセルを許可するか設定します。 |
| **特別な予約締切**       | `requires_booking_deadline`      | スイッチ       | 店舗の基本設定とは異なる予約締切を設定する場合にオンにします。                                  |
| **予約締切**             | `booking_deadline_minutes`       | 数値入力       | (上記スイッチが ON の場合に表示) 予約の何分前でオンライン予約の受付を締め切るか設定します。     |

---

##### 担当スタッフ設定

-   **UI コンポーネント**: 複数選択可能なセレクトボックス (`v-select` with `multiple` prop) またはチェックボックスのグループ
-   **ラベル**: 担当スタッフ
-   **選択肢**: この店舗に所属する全スタッフのニックネーム
-   **ヘルプテキスト**: このメニューを担当できるスタッフをすべて選択してください。
-   **name 属性**: `staff_ids[]`

---

##### 選択可能オプション設定

-   **UI コンポーネント**: 複数選択可能なセレクトボックス (`v-select` with `multiple` prop) またはチェックボックスのグループ
-   **ラベル**: 選択可能オプション
-   **選択肢**: この店舗に所属する全オプションの名称
-   **ヘルプテキスト**: このメニューに紐付けることができるオプションをすべて選択してください。
-   **name 属性**: `option_ids[]`

---

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/menus/create`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopMenuController@create`
-   **処理内容**:
    -   `ShopPolicy@update` を使用し、オーナーが自身の店舗情報を編集できるか認可チェックを行います。
    -   成功した場合は、`owner.shops.menus.create` ビューを返します。
    -   選択肢として表示するために、対象店舗に所属する全スタッフ (`$shop->staffs`) と全オプション (`$shop->options`) のリストを取得し、ビューに渡します。

##### フォーム送信 (登録処理)

-   **ルート**: `POST /owner/shops/{shop:slug}/menus`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopMenuController@store`
-   **リクエストクラス**: `App\Http\Requests\Owner\StoreShopMenuRequest`

##### バリデーション (`StoreShopMenuRequest`)

-   **認可**: `authorize`メソッド内で`ShopPolicy@update`を呼び出します。
-   **ルール**:
    -   `name`: `required`, `string`, `max:255`
    -   `price`: `required`, `integer`, `min:0`
    -   `duration`: `required`, `integer`, `min:0`
    -   `description`: `nullable`, `string`
    -   `requires_staff_assignment`: `required`, `boolean`
    -   `requires_cancellation_deadline`: `required`, `boolean`
    -   `cancellation_deadline_minutes`: `required_if:requires_cancellation_deadline,true`, `nullable`, `integer`, `min:0`
    -   `requires_booking_deadline`: `required`, `boolean`
    -   `booking_deadline_minutes`: `required_if:requires_booking_deadline,true`, `nullable`, `integer`, `min:0`
    -   `staff_ids`: `nullable`, `array`
    -   `staff_ids.*`: `integer`, `exists:shop_staffs,id` (かつ、その `shop_staff` が対象店舗に属しているかのカスタムバリデーションも必要)
    -   `option_ids`: `nullable`, `array`
    -   `option_ids.*`: `integer`, `exists:shop_options,id` (かつ、その `shop_option` が対象店舗に属しているかのカスタムバリデーションも必要)

##### 処理内容

1.  `StoreShopMenuRequest` で認可とバリデーションを実行します。
2.  バリデーションが成功した場合、`shop_menus` テーブルに新しいレコードを作成します。
3.  リクエストに `staff_ids` が含まれている場合、`shop_menu_staffs` テーブルに関連レコードを保存します (Eloquent リレーションの `attach` メソッドを利用)。
4.  リクエストに `option_ids` が含まれている場合、`shop_menu_options` テーブルに関連レコードを保存します (Eloquent リレーションの `attach` メソッドを利用)。
5.  登録後、メニュー一覧画面 (`/owner/shops/{shop:slug}/menus`) にリダイレクトし、「メニューを登録しました」という成功メッセージを表示します。

---

### メニュー編集画面

#### 機能概要

オーナーが、既存のメニューの基本情報（価格、所要時間など）や、そのメニューを担当可能なスタッフ、選択可能なオプションを編集するための画面です。また、不要になったメニューの削除もこの画面から行います。

#### 画面仕様詳細 (`/owner/shops/{shop}/menus/{menu}/edit`)

-   **ウィンドウタイトル**: `メニュー編集: {メニュー名}`

##### 表示項目一覧

リンクセクション：

-   「メニュー一覧に戻る」へのリンクをページ上部に表示

店舗ヘッダー：

-   店舗：{店舗名} (店舗 ID: {店舗 ID})

メニュー編集セクション：

-   **フォーム**:
    -   フォーム全体は `<v-card>` で囲みます。
    -   カードタイトルは「メニュー編集」とします。
    -   フォームの各項目には、編集対象メニューの既存データが初期表示されます。
    -   カードの最下部に「削除する」ボタンと「更新する」ボタンを配置します。

##### フォーム項目一覧

（メニュー登録画面と同様の項目が表示され、既存のデータが入力された状態になります）

| ラベル                 | DB カラム    | UI               | 備考                                                         |
| :--------------------- | :----------- | :--------------- | :----------------------------------------------------------- |
| **メニュー名**         | `name`       | テキスト入力     |                                                              |
| **価格**               | `price`      | 数値入力         |                                                              |
| **所要時間**           | `duration`   | 数値入力         |                                                              |
| ...                    | ...          | ...              |                                                              |
| **担当スタッフ**       | `staff_ids`  | 複数選択セレクト | 現在そのメニューに紐づいているスタッフが初期選択されます。   |
| **選択可能オプション** | `option_ids` | 複数選択セレクト | 現在そのメニューに紐づいているオプションが初期選択されます。 |

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/menus/{menu}/edit`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopMenuController@edit`
-   **処理内容**:
    1.  `ShopMenuPolicy@update` を使用し、認可チェックを行います。
    2.  ルートモデルバインディングで編集対象の `ShopMenu` モデルを取得します。
    3.  選択肢として表示するために、店舗に所属する全スタッフと全オプションの一覧も取得します。
    4.  メニューの既存データ（紐づいているスタッフとオプションの情報を含む）と、全スタッフ・全オプションの一覧をビューに渡します。

##### フォーム送信 (更新処理)

-   **ルート**: `PUT /owner/shops/{shop:slug}/menus/{menu}`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopMenuController@update`
-   **リクエストクラス**: `App\Http\Requests\Owner\UpdateShopMenuRequest` (※`option_ids`のバリデーション追加)
-   **処理内容**:
    1.  `UpdateShopMenuRequest` でバリデーションを実行します。
    2.  メニューの基本情報を更新します。
    3.  送信されたスタッフ ID の配列を使い、`sync()` メソッドでメニューとスタッフの関連付けを更新します。
    4.  送信されたオプション ID の配列を使い、`sync()` メソッドでメニューとオプションの関連付けを更新します。
    5.  更新後、メニュー一覧画面へリダイレクトします。

##### フォーム送信 (削除処理)

-   **ルート**: `DELETE /owner/shops/{shop:slug}/menus/{menu}`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopMenuController@destroy`
-   **処理内容**:
    1.  `ShopMenuPolicy@delete` で認可チェックを行います。
    2.  対象のメニューを削除します。
    3.  削除後、メニュー一覧画面へリダイレクトします。

---

### オプション一覧画面

#### 機能概要

オーナーが店舗で提供するオプションを一覧表示し、管理するための基本画面です。ここから各オプションの詳細設定や新規オプションの登録へ進みます。
一覧画面は、`docs/phase-3/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された標準パターンで実装します。

#### 画面仕様詳細 (`/owner/shops/{shop}/options`)

##### 表示項目一覧

リンクセクション：

-   店舗詳細画面へのリンクをページ上部に表示

店舗ヘッダー：

-   店舗：{店舗名} (店舗 ID: {店舗 ID})

オプション一覧セクション

-   セクションタイトル: `オプション一覧`
-   **「オプションを新規登録する」ボタン**:
    -   セクションタイトルと横並びで配置します（ボタンは右寄せ）。
    -   クリックするとオプション登録画面へ遷移します。

| カラム（ラベル） | 表示内容                                | フィルタ          | ソート | 操作           |
| :--------------- | :-------------------------------------- | :---------------- | :----- | :------------- |
| オプション名     | `shop_options.name`                     | 可 (テキスト入力) | 可     |                |
| 追加料金         | `shop_options.price` (円)               | 可 (数値範囲)     | 可     |                |
| 追加所要時間     | `shop_options.additional_duration` (分) | 可 (数値範囲)     | 可     |                |
| 登録日時         | `shop_options.created_at`               | 不可              | 可     |                |
| 操作             | 「編集」ボタン                          | 不可              | なし   | 編集画面へ遷移 |

#### バックエンド仕様

##### ページ表示時のデータ（Web コントローラから渡す情報）

`Owner\ShopOptionController@index` は、Vue コンポーネントをマウントする Blade ビューを返します。その際、以下の情報を`props`として Vue コンポーネントに渡します。

| データ名 | 型       | 説明             |
| :------- | :------- | :--------------- |
| `shop`   | `object` | 現在の店舗情報。 |

##### API エンドポイント

-   **URL**: `GET /owner/api/shops/{shop}/options`
-   **コントローラ**: `Api\Owner\ShopOptionController@index`
-   **リクエストクラス**: `App\Http\Requests\Api\Owner\IndexShopOptionsRequest`
-   **クエリパラメータ**:
    -   `name` (string): オプション名による部分一致検索。
    -   `price_from` / `price_to` (integer): 価格による範囲検索。
    -   `additional_duration_from` / `additional_duration_to` (integer): 追加所要時間による範囲検索。
    -   `sort_by` (string): ソート対象カラム。
    -   `sort_order` (string): ソート方向 (`asc` or `desc`)。
    -   `page` (integer): ページ番号。
    -   `per_page` (integer): 1 ページあたりの表示件数。

##### 処理内容

-   `ShopOptionPolicy@viewAny` により、認可されたデータのみが返却されることを保証します。
-   ログインしているオーナーの、URL で指定された店舗に紐づく `shop_options` テーブルのレコードを返却します。
-   受け取ったクエリパラメータに基づき、動的なフィルタリング、ソーティングを適用します。
-   ページネーションを適用して結果を返却します。
-   「編集」ボタンを押すと、対象オプションの編集画面 (`/owner/shops/{shop}/options/{option}/edit`) に遷移します。

---

### オプション登録画面

#### 機能概要

オーナーが、店舗で提供するオプションメニューを新規に登録するための画面です。

#### 画面仕様詳細 (`/owner/shops/{shop}/options/create`)

-   **ウィンドウタイトル**: `オプション新規登録`

##### 表示項目一覧

**リンクセクション：**

-   「オプション一覧に戻る」へのリンクをページ上部に表示

**店舗ヘッダー：**

-   店舗：{店舗名} (店舗 ID: {店舗 ID})

**オプション新規登録セクション：**

-   **フォーム**:
    -   フォーム全体は `v-card` で囲みます。
    -   カードタイトルは「オプション新規登録」とします。
    -   カードの最下部に「登録する」ボタンを配置します。

##### フォーム項目一覧

| ラベル               | DB カラム             | UI             | 備考                                                           |
| :------------------- | :-------------------- | :------------- | :------------------------------------------------------------- |
| **オプション名**     | `name`                | テキスト入力   | 必須。お客様に表示されるオプションの正式名称を入力します。     |
| **追加料金**         | `price`               | 数値入力       | 必須。オプションの価格を円単位で入力します。                   |
| **追加所要時間**     | `additional_duration` | 数値入力       | 必須。サービスの所要時間に追加される時間を分単位で入力します。 |
| **オプションの説明** | `description`         | テキストエリア | 任意。お客様に表示されるオプションの詳細な説明を入力します。   |

---

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/options/create`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopOptionController@create`
-   **処理内容**:
    -   `ShopPolicy@update` を使用し、オーナーが自身の店舗情報を編集できるか認可チェックを行います。
    -   成功した場合は、`owner.shops.options.create` ビューを返します。

##### フォーム送信 (登録処理)

-   **ルート**: `POST /owner/shops/{shop:slug}/options`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopOptionController@store`
-   **リクエストクラス**: `App\Http\Requests\Owner\StoreShopOptionRequest`

##### バリデーション (`StoreShopOptionRequest`)

-   **認可**: `authorize`メソッド内で`ShopPolicy@update`を呼び出します。
-   **ルール**:
    -   `name`: `required`, `string`, `max:255`
    -   `price`: `required`, `integer`, `min:0`
    -   `additional_duration`: `required`, `integer`, `min:0`
    -   `description`: `nullable`, `string`

##### 処理内容

1.  `StoreShopOptionRequest` で認可とバリデーションを実行します。
2.  バリデーションが成功した場合、`shop_options` テーブルに新しいレコードを作成します。
3.  登録後、オプション一覧画面 (`/owner/shops/{shop:slug}/options`) にリダイレクトし、「オプションを登録しました」という成功メッセージを表示します。

---

### オプション編集画面

#### 機能概要

オーナーが、既存のオプションの基本情報（価格、追加所要時間など）を編集するための画面です。また、不要になったオプションの削除もこの画面から行います。

#### 画面仕様詳細 (`/owner/shops/{shop}/options/{option}/edit`)

-   **ウィンドウタイトル**: `オプション編集: {オプション名}`

##### 表示項目一覧

**リンクセクション：**

-   「オプション一覧に戻る」へのリンクをページ上部に表示

**店舗ヘッダー：**

-   店舗：{店舗名} (店舗 ID: {店舗 ID})

**オプション編集セクション：**

-   **フォーム**:
    -   フォーム全体は `<v-card>` で囲みます。
    -   カードタイトルは「オプション編集」とします。
    -   フォームの各項目には、編集対象オプションの既存データが初期表示されます。
    -   カードの最下部に「削除する」ボタンと「更新する」ボタンを配置します。

##### フォーム項目一覧

（オプション登録画面と同様の項目が表示され、既存のデータが入力された状態になります）

| ラベル               | DB カラム             | UI             | 備考 |
| :------------------- | :-------------------- | :------------- | :--- |
| **オプション名**     | `name`                | テキスト入力   |      |
| **追加料金**         | `price`               | 数値入力       |      |
| **追加所要時間**     | `additional_duration` | 数値入力       |      |
| **オプションの説明** | `description`         | テキストエリア |      |

---

#### バックエンド仕様

##### データ受け渡し (ページ表示)

-   **ルート**: `GET /owner/shops/{shop:slug}/options/{option}/edit`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopOptionController@edit`
-   **処理内容**:
    1.  `ShopOptionPolicy@update` を使用し、認可チェックを行います。
    2.  ルートモデルバインディングで編集対象の `ShopOption` モデルを取得します。
    3.  オプションの既存データをビューに渡します。

##### フォーム送信 (更新処理)

-   **ルート**: `PUT /owner/shops/{shop:slug}/options/{option}`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopOptionController@update`
-   **リクエストクラス**: `App\Http\Requests\Owner\UpdateShopOptionRequest` (※新規作成)
-   **処理内容**:
    1.  `UpdateShopOptionRequest` でバリデーションを実行します。
    2.  オプションの基本情報を更新します。
    3.  更新後、オプション一覧画面へリダイレクトします。

##### フォーム送信 (削除処理)

-   **ルート**: `DELETE /owner/shops/{shop:slug}/options/{option}`
-   **コントローラ**: `App\Http\Controllers\Owner\ShopOptionController@destroy`
-   **処理内容**:
    1.  `ShopOptionPolicy@delete` で認可チェックを行います。
    2.  対象のオプションを削除します。
    3.  削除後、オプション一覧画面へリダイレクトします。
