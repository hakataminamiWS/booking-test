# スタッフ機能仕様書

## 1. 目的

このドキュメントは、スタッフ候補者、および店舗スタッフが利用する機能の UI/UX、API エンドポイント、およびデータインタラクションに関する詳細な仕様を定義します。

## 2. 対象ユーザー

-   スタッフ候補者
-   店舗スタッフ

---

## 3. 機能・画面仕様詳細

### スタッフ登録申し込み画面

#### 機能概要

スタッフ候補者が、店舗へのスタッフ登録を申請するための情報を入力する画面です。オーナーから共有された招待 URL にアクセスすると表示されます。

-   ユーザーがスタッフ登録の申し込み情報を入力・送信できる。
-   入力内容は `shop_staff_applications` テーブルに保存され、オーナーによる確認対象となる。
-   この時点では `shop_staffs` テーブルのレコードは作成しない。

#### 対象ユーザー

-   スタッフ候補者 (店舗へのスタッフ登録を希望するユーザー)

#### 画面仕様詳細 (`/shops/{shop:slug}/staff/apply`)

##### 表示項目一覧

-   非表示項目: user_id
-   表示項目: 店舗名
-   入力フォーム: 表示名
-   ボタン: 「申し込みを送信する」

| 項目    | 必須 | 型     | 備考                       |
| :------ | :--- | :----- | :------------------------- |
| user_id | ○    | hidden | ログインセッションから取得 |
| 店舗名  | ○    | string | 申し込み先の店舗名を表示   |
| 表示名  | ○    | string | フォーム入力               |

---

#### バックエンド仕様

##### データ受け渡し

-   コントローラでセッションから `user_id` を取得。
-   ルートモデルバインディングで `Shop` を取得し、店舗名を表示用にビューに渡す。
-   CSRF トークンを blade → Vue に渡す。
-   渡す方法は`docs/phase-3/ARCHITECTURE.md` の方法に従う。

##### フォーム送信

-   送信先: `POST /shops/{shop:slug}/staff/apply`
-   送信項目:
    -   user_id（hidden）
    -   name
    -   CSRF トークン

##### バリデーション

-   フロントエンド:
    -   必須項目チェック
-   バックエンド (FormRequest: `App\Http\Requests\Staff\StoreApplicationRequest`):
    -   認可チェック（未登録のユーザーであることなど）
    -   必須項目チェック (`name`)
    -   セッションの `user_id` と POST データの `user_id` の一致確認

##### 処理内容

このフォーム送信の時点では、`shop_staffs`テーブルへのレコード作成は行わない。あくまでオーナーによるスタッフ登録作業を依頼するための「申し込み」として扱う。

1.  バリデーション成功時:
    -   `shop_staff_applications` テーブルに登録。
    -   完了ページへリダイレクトし、「店舗スタッフへの申し込みが完了しました。オーナーからの承認をお待ちください。」と表示。
2.  バリデーション失敗時:
    -   画面遷移せず、エラーメッセージを表示。

##### API エンドポイント

### スタッフ管理機能

#### 1. スタッフ一覧画面

##### 機能概要
自店舗に所属する他のスタッフを確認できる画面です。

##### 画面仕様詳細 (`/shops/{shop}/staff/staffs`)
オーナー機能のスタッフ一覧 (`/owner/shops/{shop}/staffs`) と同様ですが、以下の点が異なります。
-   **編集・削除機能**: 自身のプロフィール以外は編集不可。他スタッフのプロフィールは閲覧のみ（または非表示）。
-   **新規追加**: 不可（オーナー権限）。

##### バックエンド仕様
-   **Route**: `GET /shops/{shop}/staff/staffs`
-   **Controller**: `Staff\ShopStaffController@index`
-   **API**: `GET /api/shops/{shop}/staff/staffs`

#### 2. スタッフプロフィール編集画面

##### 機能概要
ログイン中のスタッフ自身が、自身のプロフィール情報（写真、ニックネーム、自己紹介など）を編集する画面です。

##### 画面仕様詳細 (`/shops/{shop}/staff/profile/edit`)
-   **表示項目**:
    -   アイコン画像 (登録・変更・削除)
    -   表示名 (ニックネーム) *
    -   役割 (Role) - *閲覧のみ*
    -   出勤状況 - *閲覧・変更可*
    -   自己紹介文
-   **操作**: 更新ボタンのみ。

##### バックエンド仕様
-   **Route**: `GET /shops/{shop}/staff/profile/edit`
-   **Controller**: `Staff\ProfileController@edit`
-   **Update**: `PUT /shops/{shop}/staff/profile`
-   **Request**: `UpdateStaffProfileRequest`

---

### シフト管理機能

#### 1. シフト一覧画面

##### 機能概要
自店舗のスタッフのシフト状況（カレンダー）を確認できる画面です。

##### 画面仕様詳細 (`/shops/{shop}/staff/shifts`)
オーナー機能のシフト管理画面 (`/owner/shops/{shop}/shifts`) と同様です。
-   他スタッフのシフトも確認可能（予約割り当てのため）。
-   自身のシフトのみ編集可能とするか、閲覧専用とするかは要件次第（今回は閲覧専用とし、編集は自身の個別シフト編集画面で行う）。

##### バックエンド仕様
-   **Route**: `GET /shops/{shop}/staff/shifts`
-   **Controller**: `Staff\ShiftController@index`

#### 2. シフト編集画面

##### 機能概要
自身のシフト（出勤可能な曜日・時間帯）を登録・編集する画面です。

##### 画面仕様詳細 (`/shops/{shop}/staff/shifts/edit`)
-   **UI**: オーナー側でスタッフのシフトを編集する画面 (`/owner/shops/{shop}/staffs/{staff}/shifts`) と同様。
-   **機能**:
    -   曜日ごとの基本的な出勤時間の登録
    -   例外的な出勤/欠勤日の登録（必要であれば）

##### バックエンド仕様
-   **Route**: `GET /shops/{shop}/staff/shifts/edit`
-   **Controller**: `Staff\ShiftController@edit`
-   **Update**: `PUT /shops/{shop}/staff/shifts`
-   **Request**: `UpdateStaffShiftRequest`
