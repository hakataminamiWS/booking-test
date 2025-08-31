# ARCHITECTURE.md

## 1. 目的

この文書は予約サービスの **システムアーキテクチャ** を定義し、開発・運用・将来的な拡張において共通認識を持つことを目的とする。  
本システムは **マルチテナント型予約サービス** として設計する。

---

## 2. 全体構成

-   **フロントエンド**: Laravel + Blade (MPA) を基本とし、一部 Vue 3 + Vuetify + TypeScript による動的 UI
-   **バックエンド**: Laravel (API 提供 + 認証・セッション管理)
-   **データベース**: 単一 DB + `shop_id` スコープ管理（将来的に DB 分離型マルチテナントへ拡張可能）
-   **認可**: Laravel Gate / Policy によるロール判定
-   **契約管理**: Owner とシステム間の契約状態（有効/無効/期限切れ）を DB で管理し、Policy 判定に利用

---

## 3. ユーザーロールと責務

-   **Booker (予約者)**
    -   店舗に対して予約可能
-   **Staff (店舗スタッフ)**
    -   店舗の予約枠登録・予約者管理
-   **Owner (店舗オーナー)**
    -   店舗・スタッフの管理
    -   契約管理（プランや利用可否の判定）
-   **Admin (予約システム管理者)**
    -   契約管理（プランや利用可否の判定）

---

## 4. データモデル

-   `users` : 全ユーザー共通アカウント（booker / staff / owner）
-   `shops` : 店舗情報
-   `shop_user` : 中間テーブル、ユーザーと店舗の関係（role=owner/staff）
-   `bookings` : 予約情報（必ず `shop_id` を保持、`booker_id` にユーザーを紐付け）
-   `contracts` : 店舗オーナーとシステムの契約情報（プラン、期間、状態）

## 5. ディレクトリ構成

app/Http/Controllers/Booker/
app/Http/Controllers/Staff/
app/Http/Controllers/Owner/
app/Http/Controllers/Admin/
app/Services/ ← 共通ロジック
app/Policies/ ← 認可制御
resources/views/booker/
resources/views/staff/
resources/views/owner/
resources/views/admin/

---

## 6. 認可設計

-   `shop_user.role` に基づいてアクセス制御
-   Booker: 任意の店舗に予約可能（shop_id 必須）
-   Staff: 自店舗のみ操作可能
-   Owner: 自店舗操作 + 契約管理可
-   Admin: 契約管理可
-   契約状態が無効 / 期限切れの場合は、店舗機能を利用不可とする

---

## 7. API 設計方針

### 1. Booker（予約者向け）

| 機能           | Path                                            | メモ                           |
| -------------- | ----------------------------------------------- | ------------------------------ |
| 予約一覧       | `/shops/{shop_id}/bookings`                     | ログイン済み/ゲスト両対応      |
| 予約作成       | `/shops/{shop_id}/bookings/new`                 | カレンダー・空き時間選択ページ |
| 予約詳細       | `/shops/{shop_id}/bookings/{booking_id}`        | 予約確認ページ                 |
| 予約変更       | `/shops/{shop_id}/bookings/{booking_id}/edit`   | 期限内のみ可能                 |
| 予約キャンセル | `/shops/{shop_id}/bookings/{booking_id}/cancel` | 期限内のみ可能                 |

### 2. Staff（店舗スタッフ向け）

| 機能           | Path                                           | メモ                     |
| -------------- | ---------------------------------------------- | ------------------------ |
| ダッシュボード | `/shops/{shop_id}/staff/dashboard`             | 自分の店舗専用           |
| 予約一覧       | `/shops/{shop_id}/staff/bookings`              | フィルタ/ソートあり      |
| 予約作成       | `/shops/{shop_id}/staff/bookings/new`          | カレンダー・空き時間選択ページ |
| 予約詳細       | `/shops/{shop_id}/staff/bookings/{booking_id}` | 詳細確認・ステータス変更 |
| 予約可能枠管理 | `/shops/{shop_id}/staff/schedules`             | 自分のシフト登録・更新   |

### 3. Owner（店舗オーナー向け）

| 機能           | Path                                           | メモ                   |
| -------------- | ---------------------------------------------- | ---------------------- |
| ダッシュボード | `/owner/dashboard`                             | 複数店舗横断可         |
| 店舗一覧       | `/owner/shops`                                 | 自分が契約する店舗のみ |
| 店舗詳細       | `/owner/shops/{shop_id}`                       | 店舗情報確認・編集     |
| スタッフ管理   | `/owner/shops/{shop_id}/staff`                 | スタッフ一覧・追加     |
| スタッフ編集   | `/owner/shops/{shop_id}/staff/{staff_id}/edit` | 権限・シフト変更など   |
| 契約管理       | `/owner/contracts`                             | 契約更新・停止など     |

### 4. Admin（全体管理者向け）

| 機能         | Path                             | メモ                   |
| ------------ | -------------------------------- | ---------------------- |
| オーナー管理 | `/admin/owners`                  | 一覧・追加・削除       |
| オーナー詳細 | `/admin/owners/{owner_id}`       | 契約・店舗情報管理     |
| 契約管理     | `/admin/contracts/{contract_id}` | 契約ステータス変更など |
