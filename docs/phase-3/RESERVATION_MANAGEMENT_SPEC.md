# 予約管理機能仕様書

このドキュメントは、予約管理機能のUI/UX、APIエンドポイント、およびデータインタラクションに関する詳細な仕様を定義します。

## 1. 目的

予約管理機能の設計と実装において、関係者間の共通認識を確立することを目的とします。

## 2. 対象ユーザー

-   Staff (店舗スタッフ)
-   Owner (店舗オーナー)

## 3. 機能概要

予約の閲覧、検索、詳細確認、ステータス変更（確定、キャンセルなど）、および必要に応じた編集・削除機能を提供します。

## 4. 画面仕様詳細

### 4.1. 予約一覧画面

-   **URL**: `/shops/{shop_slug}/staff/bookings` (Staff), `/owner/shops/{shop_slug}/bookings` (Owner)
-   **UI要素**:
    *   検索フィルター (日付範囲、ステータス、スタッフ、メニュー、顧客名など)
    *   ソート機能 (予約日時、顧客名など)
    *   ページネーション
    *   予約情報のリスト表示 (予約日時、顧客名、メニュー、スタッフ、ステータスなど)
    *   各予約の詳細画面へのリンク
-   **データフロー**:
    *   `GET /shops/{shop_slug}/staff/api/bookings` (Staff)
    *   `GET /owner/shops/{shop_slug}/api/bookings` (Owner)
    *   レスポンス例: (予約一覧のJSON構造)

### 4.2. 予約詳細画面

-   **URL**: `/shops/{shop_slug}/staff/bookings/{booking_id}` (Staff), `/owner/shops/{shop_slug}/bookings/{booking_id}` (Owner)
-   **UI要素**:
    *   予約情報の詳細表示 (顧客情報、予約日時、メニュー、スタッフ、ステータス、メモなど)
    *   ステータス変更ボタン (確定、キャンセルなど)
    *   編集ボタン
    *   削除ボタン
-   **データフロー**:
    *   `GET /shops/{shop_slug}/staff/api/bookings/{booking_id}` (Staff)
    *   `GET /owner/shops/{shop_slug}/api/bookings/{booking_id}` (Owner)
    *   `POST /api/bookings/{booking_id}/statuses` (ステータス変更)
    *   `PUT /shops/{shop_slug}/staff/api/bookings/{booking_id}` (メモ等の編集)
    *   `DELETE /shops/{shop_slug}/staff/api/bookings/{booking_id}` (削除)

## 5. API設計

### 5.1. 予約一覧取得

-   **エンドポイント**: `GET /shops/{shop_slug}/staff/api/bookings` (Staff), `GET /owner/shops/{shop_slug}/api/bookings` (Owner)
-   **クエリパラメータ**: フィルター、ソート、ページネーション関連
-   **レスポンス**: 予約情報のリスト (JSON)

### 5.2. 予約詳細取得

-   **エンドポイント**: `GET /shops/{shop_slug}/staff/api/bookings/{booking_id}` (Staff), `GET /owner/shops/{shop_slug}/api/bookings/{booking_id}` (Owner)
-   **レスポンス**: 単一の予約情報 (JSON)

### 5.3. 予約ステータス変更

-   **エンドポイント**: `POST /api/bookings/{booking_id}/statuses`
-   **リクエストボディ**: `{ "status": "confirmed" }` のように、新しいステータスを送信。
-   **レスポンス**: 作成された新しいステータス情報 (JSON)

### 5.4. 予約更新 (メモなど)

-   **エンドポイント**: `PUT /shops/{shop_slug}/staff/api/bookings/{booking_id}`
-   **リクエストボディ**: 変更する予約情報（メモなど）
-   **レスポンス**: 更新された予約情報 (JSON)

### 5.4. 予約削除

-   **エンドポイント**: `DELETE /shops/{shop_slug}/staff/api/bookings/{booking_id}`
-   **レスポンス**: 成功/失敗を示すメッセージ

## 6. 認可 (Authorization)

-   Laravel Policy (`BookingPolicy`) を利用し、各ユーザーロールがアクセスできる予約情報と操作を制御します。
    *   Staff: 自身の所属する店舗の予約のみ操作可能です。
    *   Owner: 自身の契約する店舗の予約のみ操作可能です。
-   Admin（システム管理者）は、原則として店舗の予約情報を直接操作する権限を持ちません。

## 7. データモデルとの関連

-   `Booking` モデル
-   `Shop` モデル
-   `User` モデル (Booker, Staff)
-   `Menu` モデル