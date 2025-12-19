# 店舗スタッフ向け予約者管理機能仕様書

## 1. 目的

店舗スタッフ (Staff) が、自店舗の顧客情報（予約者）を一覧で確認し、検索、登録、編集を行うための機能仕様を定義します。

## 2. 対象ユーザー

-   Staff (店舗スタッフ)

---

## 3. 機能・画面仕様詳細

### 予約者一覧画面

#### 機能概要

スタッフが店舗の顧客情報を一覧表示し、管理するための基本画面です。ここから各予約者の詳細確認や新規登録へ進みます。
一覧画面は、`docs/INDEX_PAGE_IMPLEMENTATION_PATTERNS.md`で定義された標準パターンで実装します。

#### 画面仕様詳細 (`/shops/{shop}/staff/bookers`)

##### 表示項目一覧

店舗ヘッダー：

-   `店舗：{店舗名}` の共通ヘッダーを表示します。

予約者一覧セクション：

-   セクションタイトル: `予約者一覧`
-   **「予約者を新規登録する」ボタン**:
    -   セクションタイトルと横並びで配置します（ボタンは右寄せ）。
    -   クリックすると予約者登録画面 (`/shops/{shop}/staff/bookers/create`) へ遷移します。

| カラム（ラベル） | 表示内容 | フィルタ | ソート | 操作 |
| :--- | :--- | :--- | :--- | :--- |
| **会員番号** | `shop_bookers.number` | 可 (テキスト入力) | 可 | |
| **名前** | `shop_bookers.name` | 可 (テキスト入力) | 可 | |
| **よみかた** | `shop_bookers_crm.name_kana` | 可 (テキスト入力) | 可 | |
| **連絡先メールアドレス** | `shop_bookers.contact_email` | 可 (テキスト入力) | 可 | |
| **連絡先電話番号** | `shop_bookers.contact_phone` | 可 (テキスト入力) | 不可 | |
| **最終予約日時** | `shop_bookers_crm.last_booking_at` | 可 (日付範囲) | 可 | |
| **予約回数** | `shop_bookers_crm.booking_count` | 可 (数値範囲) | 可 | |
| **操作** | 「編集」ボタン | 不可 | なし | 編集画面へ遷移 |

#### バックエンド仕様

##### ページ表示時のデータ

`Staff\ShopBookerController@index` は、Vue コンポーネントをマウントする Blade ビューを返します。

| データ名 | 型 | 説明 |
| :--- | :--- | :--- |
| `shop` | `object` | 現在の店舗情報。 |

##### API エンドポイント

-   **URL**: `GET /api/shops/{shop}/staff/bookers`
-   **コントローラ**: `Api\Staff\ShopBookerController@index`
-   **リクエストクラス**: `App\Http\Requests\Api\Staff\IndexShopBookersRequest`
-   **クエリパラメータ**: オーナー機能と同様。

---

### 予約者登録画面

#### 機能概要

スタッフが、店舗の顧客（予約者）を新規に手動で登録するための画面です。

#### 画面仕様詳細 (`/shops/{shop}/staff/bookers/create`)

-   **ウィンドウタイトル**: `予約者新規登録`
-   **画面構成**: オーナー機能と同様。

##### バックエンド仕様

-   **ルート**: `GET /shops/{shop}/staff/bookers/create`
-   **コントローラ**: `Staff\ShopBookerController@create`
-   **処理内容**:
    -   `ShopBookerPolicy@create` (または `StaffShopBookerPolicy`) で認可チェック。

###### 登録処理 (`store`)

-   **ルート**: `POST /shops/{shop}/staff/bookers`
-   **コントローラ**: `Staff\ShopBookerController@store`
-   **リクエストクラス**: `StoreShopBookerRequest` (オーナーと共用、またはスタッフ用継承クラス)
    -   完了後、予約者一覧へリダイレクト。

---

### 予約者編集画面

#### 機能概要

スタッフが、既存の予約者の情報を編集するための画面です。

#### 画面仕様詳細 (`/shops/{shop}/staff/bookers/{booker}/edit`)

-   **ウィンドウタイトル**: `予約者編集: {予約者名}`
-   **画面構成**: オーナー機能と同様。
    -   スタッフによる削除権限の有無は要検討（現時点ではオーナー機能同様、ボタン非活性または権限チェックで制御）。

##### バックエンド仕様

-   **ルート**: `GET /shops/{shop}/staff/bookers/{booker}/edit`
-   **コントローラ**: `Staff\ShopBookerController@edit`

###### 更新処理 (`update`)

-   **ルート**: `PUT /shops/{shop}/staff/bookers/{booker}`
-   **コントローラ**: `Staff\ShopBookerController@update`
-   **リクエストクラス**: `UpdateShopBookerRequest` (オーナーと共用、またはスタッフ用継承クラス)
