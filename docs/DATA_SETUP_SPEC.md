# データセットアップ仕様書

このドキュメントは、予約機能のテストに必要なデータセットアップの仕様を定義します。特に、予約可能時間枠の計算ロジックを検証するために必要なデータに焦点を当てます。

## 1. 店舗 (shops) データ

-   `time_slot_interval`: 予約枠の生成間隔 (例: `30` 分)
-   `cancellation_deadline_minutes`: キャンセル可能な期限 (例: `1440` 分前)
-   `booking_deadline_minutes`: 予約受付期限 (例: `60` 分前)

## 2. 店舗の休日データ

-   **`shop_recurring_holidays`**: 定休日データ (例: `day_of_week: 1` で月曜定休)
-   **`shop_specific_holidays`**: 特別休業日データ (例: `date: '2025-12-31'`, `name: '年末年始休業'`) 

## 3. メニューとオプションのデータ

-   **`menus`**: `price`, `duration`, `description` を設定。
-   **`options`**: `price`, `duration` を設定。
-   **`menu_option`**: テストシナリオに合わせて、メニューとオプションを紐付け。

## 4. スタッフスケジュール (staff_schedules) データ

-   `staff_user_id`: 対象となるスタッフのID
-   `available_start_at`: 予約受付を開始する日時 (`datetime`)
-   `available_end_at`: 予約受付を終了する日時 (`datetime`)

## 5. 既存の予約 (bookings) データ

-   `shop_id`, `booker_id`, `menu_id`, `staff_id`
-   `start_at`: 予約開始日時 (UTC)
-   `menu_name`, `menu_price`, `menu_duration`, `staff_name` などのスナップショット情報

## 6. テストシナリオ例

以下のシナリオを検証するためのデータセットアップを考慮します。

-   営業時間内の予約
-   営業時間外の予約
-   **定休日・特別休業日**の予約
-   スタッフ指名時の空き
-   **オプション選択時**の合計時間・料金を考慮した空き
-   既存予約との競合
-   各種予約受付期限のチェック
-   過去の時間の予約
