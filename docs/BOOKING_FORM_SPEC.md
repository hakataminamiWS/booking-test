# 予約機能仕様書 (v1.4)

## 1. 対象機能一覧

- Web 予約フォーム（スマホ対応）
- カレンダーから空き時間選択
- **オプションメニュー選択（任意）**
- スタッフ指名（任意）
- メニューごとの所要時間自動反映
- 予約内容の確認ページ
- 自動返信メール送信

---

## 2. アーキテクチャ方針

- **UI構築**: Vue 3 + Vuetify + TypeScript を用いて、動的でインタラクティブなUIを構築。
- **データ取得**: Laravel が提供する JSON API から非同期で取得。
- **予約実行**: 最終予約は Vue で管理するデータを `<input type="hidden">` にセットしてフォーム POST、Laravel 側で処理後リダイレクト。
- **日時管理**: 予約日時は ISO 8601 形式で保存、UTC 基準で管理し、フロントで店舗ローカルタイムに変換。
- **セキュリティ**: Laravel Policy を利用し、リソース（店舗情報、予約情報など）へのアクセス認可を徹底する。

---

## 3. 画面仕様詳細

予約フォームはステップ形式で構成。

- **URL**: `/shops/{shop_slug}/bookings/create`
- **使用コンポーネント**: Vuetify `v-stepper`

---

### ステップ1: メニュー選択

- **目的**: 希望メニュー選択
- **UI**: メニュー名・価格・所要時間をリスト表示。`v-radio-group` で1つだけ選択可能。
- **データフロー**:
  1. `GET /api/shops/{shop_slug}/menus` で取得。
  2. Vue 内で選択された `menu_id` と `duration` を保持。

---

### ステップ1.5: オプション選択（任意）

- **目的**: 希望するオプションを追加で選択する。
- **UI**: `v-checkbox` を使用し、複数選択を可能にする。前のステップで選択したメニューに紐づくオプションがない場合は、このステップはスキップされる。
- **データフロー**:
  1. ステップ1でメニューが選択された後、`GET /api/menus/{menu_id}/options` で、そのメニューに追加可能なオプションのリストを取得する。
  2. Vue 内で選択された `option_ids` のリストと、オプションによる追加所要時間・追加料金を保持する。

---

### ステップ2: スタッフ指名（任意）

- **UI**: `v-select` を使用し、スタッフを指名する。デフォルトは「指名なし」。
- **データフロー**: `staff_id` を Vue 内で保持。

---

### ステップ3: 日時選択

- **UI**: `v-date-picker` と `v-chip-group` を使用。
- **データフロー**: `GET /api/shops/{shop_slug}/available-slots` で空き時間を取得。Vue 内で `start_at` を保持。

---

### ステップ4: お客様情報入力

- **UI**: `v-text-field` にて氏名・メール・電話番号・メモ（任意）入力。
- **データフロー**: Vue 内で `name`, `email`, `tel`, `memo` を保持。

---

### ステップ5: 予約内容確認

- **UI**:
  - 選択・入力した全情報（メニュー、**追加オプション**、スタッフ、日時、お客様情報）を表示。
  - 合計料金、合計所要時間を表示。
  - 「内容を修正」 → 該当ステップに戻る。
  - 「予約を確定する」 → フォームをPOST送信。
- **データフロー**:
  - Vue 内の全情報（`option_ids` を含む）を `<input type="hidden">` に反映。
  - 送信後、Laravel が処理して完了ページへリダイレクト。

---

## 4. バックエンド仕様

### データモデル
本仕様書で言及されるデータは、以下の主要モデルと関連する。詳細は `DATABASE_SCHEMA.md` を参照。
- `Shop`, `Menu`, `Booking`, `User`
- `Option`, `MenuOption`, `BookingOption`

### 予約処理

- **ルート**: `POST /shops/{shop_slug}/bookings`
- **コントローラ**: `BookingsController@store`
- **処理内容**:
  1. `FormRequest` によるバリデーションと認可チェック。リクエストには `option_ids` や `requested_staff_id` も含まれうる。
  2. 二重予約チェック。
  3. `bookings` テーブルにデータ永続化。この際、顧客が希望した担当者は `requested_staff_id` に保存する。`assigned_staff_id` は、この段階ではNULLか、指名ありの場合は同IDが保存される。
  4. **`option_ids` が存在する場合、`booking_option` テーブルに、予約IDと各オプションIDの組み合わせを保存。**
  5. 自動返信メール送信ジョブをキューに追加。
  6. 完了ページへリダイレクト。

### 予約可能時間枠の計算ロジック

詳細は `docs/AVAILABILITY_CALCULATION_LOGIC.md` を参照。
 「予約を確定する」 → フォームをPOST送信。
- **データフロー**:
  - Vue 内の全情報（`option_ids` を含む）を `<input type="hidden">` に反映。
  - 送信後、Laravel が処理して完了ページへリダイレクト。

---

## 4. バックエンド仕様

### データモデル
本仕様書で言及されるデータは、以下の主要モデルと関連する。詳細は `DATABASE_SCHEMA.md` を参照。
- `Shop`, `Menu`, `Booking`, `User`
- `Option`, `MenuOption`, `BookingOption`

### 予約処理

- **ルート**: `POST /shops/{shop_slug}/bookings`
- **コントローラ**: `BookingsController@store`
- **処理内容**:
  1. `FormRequest` によるバリデーションと認可チェック。リクエストには `option_ids` の配列も含まれうる。
  2. 二重予約チェック。
  3. `bookings` テーブルにデータ永続化。
  4. **`option_ids` が存在する場合、`booking_option` テーブルに、予約IDと各オプションIDの組み合わせを保存。**
  5. 自動返信メール送信ジョブをキューに追加。
  6. 完了ページへリダイレクト。

### 予約可能時間枠の計算ロジック

詳細は `docs/AVAILABILITY_CALCULATION_LOGIC.md` を参照。
