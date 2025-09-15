# ARCHITECTURE.md

## 1. 目的

この文書は予約サービスの **システムアーキテクチャ** を定義し、開発・運用・将来的な拡張において共通認識を持つことを目的とする。

---

## 2. アーキテクチャ方針

-   **フロントエンド**: Laravel + Blade (MPA) を基本とし、一部 Vue 3 + Vuetify + TypeScript による動的 UI
-   **バックエンド**: Laravel (API 提供 + 認証・セッション管理)
-   **データベース**: 単一 DB + `shop_id` スコープ管理（将来的に DB 分離型マルチテナントへ拡張可能）
-   **認可**: Laravel Gate / Policy によるロール判定
-   **契約管理**: Owner とシステム間の契約状態（有効/無効/期限切れ）を DB で管理し、Policy 判定に利用
-   **責務分担**:
    -   Laravel + Blade: ページ遷移・認証・セッション・CSRF管理・フォーム処理
    -   Vue + Vuetify: 予約フォームUI、予約一覧 (フィルタ/ソート/ページネーション)
-   **API**: Laravel コントローラから JSON を返却、Vue が描画更新

---

## 3. 認可設計

詳細なユーザーロールと責務については `AUTHORIZATION_SPEC.md` を参照。

-   **基本方針**:
    -   `shop_user.role` に基づいてアクセス制御
    -   Booker: 任意の店舗に予約可能（shop_slug 必須）
    -   Staff: 自店舗のみ操作可能
    -   Owner: 自店舗操作 + 契約管理可
    -   Admin: 契約管理可
    -   契約状態が無効 / 期限切れの場合は、店舗機能を利用不可とする

---

## 4. マルチテナントのデータ分離戦略

本システムは、オーナーやスタッフが自身の所属する店舗の情報のみを閲覧・操作できるよう、複数の仕組みを組み合わせてデータ分離を実現する。

### 4.1. グローバルスコープによる自動的な絞り込み

-   **目的**: 開発者の `where('shop_id', ...)` の記述漏れによる意図しない情報漏洩を、システムレベルで防止する。
-   **仕組み**:
    -   `Booking`, `Menu`, `Option` など、店舗に所属する主要なモデルには **グローバルスコープ** を適用する。
    -   このスコープは、現在ログインしているユーザーがオーナーまたはスタッフの場合、自動的にそのユーザーが所属する `shop_id` でクエリを絞り込む `WHERE` 句を追加する。
    -   これにより、例えばコントローラーで `Booking::all()` を呼び出した場合でも、実際には自店舗の予約のみが取得される。

### 4.2. Policyによる個別アクセスの認可

-   **目的**: URLを直接操作するなどの意図的な不正アクセスを防ぎ、個別のデータに対する操作権限を厳密に検証する。
-   **仕組み**:
    -   各モデルに対応する Policy (`BookingPolicy`, `ShopPolicy` など) を用意する。
    -   `update` や `delete` などのメソッド内で、操作対象のデータ（例: `$booking`）の `shop_id` が、操作しようとしているユーザーの所属する `shop_id` と一致するかを検証する。
    -   コントローラーの各メソッドの冒頭で `$this->authorize(...)` を呼び出すことで、この検証を強制する。

この2つの仕組みを組み合わせることで、テナントデータの安全な分離を堅牢に実現する。

---

## 5. データモデルとロジック

詳細なデータモデル（ER図など）は `DATABASE_SCHEMA.md` を参照。

### 5.1. 顧客アカウントモデルと統合ロジック

本システムでは、ゲスト予約と会員登録ユーザーを柔軟に紐付けるため、「マスターアカウントモデル」を採用する。

-   **モデルの役割**:
    -   **`users` (顧客台帳)**: 顧客の本体を表すマスターテーブル。
    -   **`bookers` (来店カード)**: 予約に紐づく識別子。`users`に多対一で紐づく。
    -   **`bookings` (予約票)**: 予約情報。`bookers`に紐づく。
-   **アカウント作成フロー**:
    1.  **ゲスト予約時**: `users`に`is_guest=true`の「仮マスターアカウント」を作成し、`bookers`と`bookings`を紐付ける。
    2.  **会員登録（SNSログイン）時**: `users`に`is_guest=false`の「正規マスターアカウント」を作成する。
-   **顧客統合（名寄せ）フロー**:
    -   店舗スタッフは、ゲスト予約の`bookers`が指す`user_id`を、正規マスターアカウントの`user_id`に更新できる。
    -   これにより、`bookings`レコードを変更せずに予約履歴を統合する。
-   **予約履歴の表示ロジック**:
    -   **店舗側**: `user_id`に紐づく全ての`bookers`を辿り、全予約を表示。
    -   **ユーザー側**: `user_id`に紐づく`bookers`を辿り、`type='login'`の予約のみを表示。

### 5.2. データ削除方針

本システムでは、ユーザーの退会やメニューの廃止など、マスターデータの物理削除が行われることを前提として設計する。

-   **参照整合性の担保**:
    1.  **モデルイベントの利用**: Laravelの`deleting`または`deleted`イベントでマスターレコードの削除を検知する。
    2.  **関連IDのNULL化**: 削除イベント後、`bookings`テーブルの関連ID（`menu_id`, `staff_id`など）を自動的に`NULL`に更新する。

これにより、個人情報との関連を断ち切りつつ、予約記録の匿名性とスナップショットとしての独立性を担保する。

---

## 6. API 設計方針

### 6.1. Booker（予約者向け）

| 機能           | Path                                            |
| -------------- | ----------------------------------------------- |
| 予約一覧       | `/shops/{shop_slug}/bookings`                     |
| 予約作成       | `/shops/{shop_slug}/bookings/new`                 |
| 予約詳細       | `/shops/{shop_slug}/bookings/{booking_id}`        |
| 予約変更       | `/shops/{shop_slug}/bookings/{booking_id}/edit`   |
| 予約キャンセル | `/shops/{shop_slug}/bookings/{booking_id}/cancel` |

### 6.2. Staff（店舗スタッフ向け）

| 機能           | Path                                           |
| -------------- | ---------------------------------------------- |
| ダッシュボード | `/shops/{shop_slug}/staff/dashboard`             |
| 予約一覧       | `/shops/{shop_slug}/staff/bookings`              |
| 予約作成       | `/shops/{shop_slug}/staff/bookings/new`          |
| 予約詳細       | `/shops/{shop_slug}/staff/bookings/{booking_id}` |
| 予約可能枠管理 | `/shops/{shop_slug}/staff/schedules`             |

### 6.3. Owner（店舗オーナー向け）

| 機能           | Path                                           |
| -------------- | ---------------------------------------------- |
| ダッシュボード | `/owner/dashboard`                             |
| 店舗一覧       | `/owner/shops`                                 |
| 店舗詳細       | `/owner/shops/{shop_slug}`                       |
| スタッフ管理   | `/owner/shops/{shop_slug}/staff`                 |
| スタッフ編集   | `/owner/shops/{shop_slug}/staff/{staff_id}/edit` |
| 契約管理       | `/owner/contracts`                             |

### 6.4. Admin（全体管理者向け）

| 機能         | Path                             |
| ------------ | -------------------------------- |
| オーナー管理 | `/admin/owners`                  |
| オーナー詳細 | `/admin/owners/{owner_id}`       |
| 契約管理     | `/admin/contracts/{contract_id}` |

---

## 7. ディレクトリ構成

-   `app/Http/Controllers/Booker/`
-   `app/Http/Controllers/Staff/`
-   `app/Http/Controllers/Owner/`
-   `app/Http/Controllers/Admin/`
-   `app/Services/` ← 共通ロジック
-   `app/Policies/` ← 認可制御
-   `resources/views/booker/`
-   `resources/views/staff/`
-   `resources/views/owner/`
-   `resources/views/admin/`
