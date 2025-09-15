# データベーススキーマ定義

このドキュメントは、予約システムのデータベーススキーマを定義します。これが唯一の正しい定義（Single Source of Truth）です。

## 設計方針

-   **命名規則**: テーブル名は複数形のスネークケース（`snake_case`）、カラム名もスネークケースとします。
-   **ID**: 主キーは `id` (`bigint`, `PK`, `AI`) とし、データベース内部のリレーションで利用します。外部に公開する ID は `uuid` (`char(36)`) を別途用意します。
-   **外部キー**: `onDelete` の挙動を明記します。`CASCADE` は親レコードの削除で子レコードも削除、`RESTRICT` は子レコードが存在する場合は親レコードを削除させません。

---

## テーブル定義

### `users`

システムの全ユーザー（予約者、スタッフ、オーナー、管理者）の基本情報を管理します。

| カラム名     | データ型    | 制約                         | 説明                           |
| :----------- | :---------- | :--------------------------- | :----------------------------- |
| `id`         | `bigint`    | `PK`, `AI`                   | 主キー（内部 ID）              |
| `public_id`  | `char(26)`  | `Not NULL`, `Unique`         | 公開用 ID (ULID)               |
| `is_guest`   | `boolean`   | `Not NULL`, `Default: false` | ゲストユーザーかどうかのフラグ |
| `created_at` | `timestamp` |                              | 作成日時                       |
| `updated_at` | `timestamp` |                              | 更新日時                       |

### `user_oauth_identities`

外部認証プロバイダ（Google, LINE 等）の識別子を管理します。

| カラム名         | データ型                     | 制約                                 | 説明                        |
| :--------------- | :--------------------------- | :----------------------------------- | :-------------------------- |
| `id`             | `bigint`                     | `PK`, `AI`                           | 主キー                      |
| `user_id`        | `bigint`                     | `FK (users.id)`, `onDelete: CASCADE` | ユーザー ID                 |
| `provider`       | `varchar(255)`               | `Not NULL`                           | プロバイダ名 (例: 'google') |
| `provider_sub`   | `varchar(255)`               | `Not NULL`                           | プロバイダのユーザー識別子  |
| `created_at`     | `timestamp`                  |                                      | 作成日時                    |
| `updated_at`     | `timestamp`                  |                                      | 更新日時                    |
| **ユニーク制約** | (`provider`, `provider_sub`) |                                      |                             |

### `bookers`

予約者としての「識別子（来店カード）」を管理します。予約は必ずこの識別子を経由してマスターアカウント(`users`)に紐付きます。

| カラム名     | データ型    | 制約                                 | 説明                                    |
| :----------- | :---------- | :----------------------------------- | :-------------------------------------- |
| `id`         | `bigint`    | `PK`, `AI`                           | 予約者識別子 ID                         |
| `user_id`    | `bigint`    | `FK (users.id)`, `onDelete: CASCADE` | この識別子が属するマスターアカウント ID |
| `created_at` | `timestamp` |                                      | 作成日時                                |
| `updated_at` | `timestamp` |                                      | 更新日時                                |

### `admins`

システム管理者の情報を管理します。将来的な管理者固有設定のために分離されています。

| カラム名     | データ型       | 制約                                           | 説明        |
| :----------- | :------------- | :--------------------------------------------- | :---------- |
| `id`         | `bigint`       | `PK`, `AI`                                     | 主キー      |
| `user_id`    | `bigint`       | `FK (users.id)`, `onDelete: CASCADE`, `Unique` | ユーザー ID |
| `name`       | `varchar(255)` | `Not NULL`                                     | 管理者名    |
| `created_at` | `timestamp`    |                                                | 作成日時    |
| `updated_at` | `timestamp`    |                                                | 更新日時    |

### `owners`

店舗オーナーの情報を管理します。`contracts` や `shops` とは独立して、「オーナーである」という役割をシステムレベルで定義します。

| カラム名     | データ型       | 制約                                           | 説明        |
| :----------- | :------------- | :--------------------------------------------- | :---------- |
| `id`         | `bigint`       | `PK`, `AI`                                     | 主キー      |
| `user_id`    | `bigint`       | `FK (users.id)`, `onDelete: CASCADE`, `Unique` | ユーザー ID |
| `name`       | `varchar(255)` | `Not NULL`                                     | オーナー名  |
| `created_at` | `timestamp`    |                                                | 作成日時    |
| `updated_at` | `timestamp`    |                                                | 更新日時    |

### `roles`

システム内の役割（ロール）定義を管理します。

| カラム名      | データ型       | 制約                 | 説明                            |
| :------------ | :------------- | :------------------- | :------------------------------ |
| `id`          | `bigint`       | `PK`, `AI`           | 主キー                          |
| `name`        | `varchar(255)` | `Not NULL`, `Unique` | ロール名 (例: 'owner', 'staff') |
| `description` | `text`         | `Nullable`           | ロールの説明                    |
| `created_at`  | `timestamp`    |                      | 作成日時                        |
| `updated_at`  | `timestamp`    |                      | 更新日時                        |

### `shops`

店舗の基本情報を管理します。

| カラム名                        | データ型       | 制約                                 | 説明                                                                                                      |
| :------------------------------ | :------------- | :----------------------------------- | :-------------------------------------------------------------------------------------------------------- |
| `id`                            | `bigint`       | `PK`, `AI`                           | 主キー                                                                                                    |
| `owner_user_id`                 | `bigint`       | `FK (users.id)`, `onDelete: CASCADE` | この店舗を所有するオーナーのユーザー ID                                                                   |
| `slug`                          | `varchar(255)` | `Not NULL`, `Unique`                 | URL で利用する、URL セーフな一意の識別子 (例: 'hakata-minami-store')                                      |
| `name`                          | `varchar(255)` | `Not NULL`                           | 店舗名                                                                                                    |
| `time_slot_interval`            | `integer`      | `Not NULL`, `Default: 30`            | 予約枠の表示単位（分）                                                                                    |
| `cancellation_deadline_minutes` | `integer`      | `Not NULL`, `Default: 1440`          | キャンセル可能な期限（分単位）                                                                            |
| `booking_deadline_minutes`      | `integer`      | `Not NULL`, `Default: 0`             | 店舗の基本予約締め切り（分単位）。0 は直前まで可。                                                        |
| `booking_confirmation_type`     | `varchar(255)` | `Not NULL`, `Default: 'automatic'`   | 予約承認方法 (`automatic` or `manual`)                                                                    |
| `status`                        | `varchar(255)` | `Not NULL`                           | 店舗ステータス (例: 'active', 'inactive', 'deleting')                                                     |
| `timezone`                      | `varchar(255)` | `Not NULL`, `Default: 'Asia/Tokyo'`  | 店舗のタイムゾーン。予約可能時間の計算など、店舗のローカル時間に依存する処理の基準として利用される。現在は日本の店舗を想定しているため、デフォルト値は `'Asia/Tokyo'` となっている。 |
| `created_at`                    | `timestamp`    |                                      | 作成日時                                                                                                  |
| `updated_at`                    | `timestamp`    |                                      | 更新日時                                                                                                  |

### `shop_regular_holidays`

店舗の定休日（毎週の特定の曜日）を管理します。

| カラム名      | データ型  | 制約                                 | 説明                     |
| :------------ | :-------- | :----------------------------------- | :----------------------- |
| `id`          | `bigint`  | `PK`, `AI`                           | 主キー                   |
| `shop_id`     | `bigint`  | `FK (shops.id)`, `onDelete: CASCADE` | 店舗 ID                  |
| `day_of_week` | `integer` | `Not NULL`                           | 曜日 (0=日曜, 1=月曜...) |
| `is_closed`   | `boolean` | `Not NULL`, `Default: true`          | この曜日が定休日かどうか |

### `shop_staff_shift_constraints`

店舗スタッフが自身の稼働時間（シフト）を登録する際の、時間的な制約を定義します。例えば、このテーブルで月曜日の制約が「09:00-18:00」と設定されている場合、スタッフは月曜日に「08:00」から開始したり、「19:00」に終了するシフトを登録できなくなります。この設定は、あくまでスタッフのシフト登録画面でのバリデーションにのみ使用され、顧客向けの予約可能枠の計算には一切影響しません。

| カラム名         | データ型  | 制約             | 説明                               |
| :--------------- | :-------- | :--------------- | :--------------------------------- |
| `id`             | `bigint`  | `PK`, `AI`       | 主キー                             |
| `shop_id`        | `bigint`  | `FK(shops.id)`   | 店舗 ID                            |
| `day_of_week`    | `integer` | `Not NULL`       | 曜日 (0=日曜, 1=月曜...)           |
| `min_time`       | `time`    | `Nullable`       | シフト登録可能な最も早い開始時刻   |
| `max_time`       | `time`    | `Nullable`       | シフト登録可能な最も遅い終了時刻   |
| `is_unavailable` | `boolean` | `Default: false` | この曜日のシフト登録を不可にするか |

### `shop_specific_holidays`

店舗の特別休業日（祝日、年末年始など）を管理します。

| カラム名     | データ型    | 制約                                 | 説明                              |
| :----------- | :---------- | :----------------------------------- | :-------------------------------- |
| `id`         | `bigint`    | `PK`, `AI`                           | 主キー                            |
| `shop_id`    | `bigint`    | `FK (shops.id)`, `onDelete: CASCADE` | 店舗 ID                           |
| `date`       | `date`      | `Not NULL`                           | 休日                              |
| `name`       | `text`      | `Nullable`                           | 休日の名前 (例: 「年末年始休暇」) |
| `created_at` | `timestamp` |                                      | 作成日時                          |
| `updated_at` | `timestamp` |                                      | 更新日時                          |

### `menus`

各店舗が提供するメニュー情報を管理します。

| カラム名                    | データ型       | 制約                                 | 説明                                                                                              |
| :-------------------------- | :------------- | :----------------------------------- | :------------------------------------------------------------------------------------------------ |
| `id`                        | `bigint`       | `PK`, `AI`                           | 主キー                                                                                            |
| `shop_id`                   | `bigint`       | `FK (shops.id)`, `onDelete: CASCADE` | 店舗 ID                                                                                           |
| `name`                      | `varchar(255)` | `Not NULL`                           | メニュー名                                                                                        |
| `price`                     | `integer`      | `Not NULL`                           | 価格                                                                                              |
| `description`               | `text`         | `Nullable`                           | メニューの説明                                                                                    |
| `duration`                  | `integer`      | `Not NULL`                           | 所要時間（分）                                                                                    |
| `booking_deadline_minutes`  | `integer`      | `Nullable`                           | メニュー固有の予約締め切り（分単位）。NULL の場合は店舗設定に従う。                               |
| `requires_staff_assignment` | `boolean`      | `Not NULL`, `Default: true`          | 担当者の割り当てが必須かどうかのフラグ。`true`なら必須、`false`なら不要（＝どのスタッフでも可）。 |
| `created_at`                | `timestamp`    |                                      | 作成日時                                                                                          |
| `updated_at`                | `timestamp`    |                                      | 更新日時                                                                                          |

### `options`

オプションメニューの情報を管理します。

| カラム名              | データ型       | 制約                                 | 説明                                    |
| :-------------------- | :------------- | :----------------------------------- | :-------------------------------------- |
| `id`                  | `bigint`       | `PK`, `AI`                           | 主キー                                  |
| `shop_id`             | `bigint`       | `FK (shops.id)`, `onDelete: CASCADE` | 店舗 ID                                 |
| `name`                | `varchar(255)` | `Not NULL`                           | オプション名 (例: 「炭酸泉シャンプー」) |
| `price`               | `integer`      | `Not NULL`                           | 追加料金                                |
| `additional_duration` | `integer`      | `Not NULL`                           | 追加の所要時間（分）                    |
| `description`         | `text`         | `Nullable`                           | オプションの説明                        |
| `created_at`          | `timestamp`    |                                      | 作成日時                                |
| `updated_at`          | `timestamp`    |                                      | 更新日時                                |

### `menu_option`

メニューに対して、選択可能なオプションを定義するための中間テーブル。ここにレコードが存在することで、その組み合わせが許可されていることを示します。

| カラム名    | データ型 | 制約                                   | 説明          |
| :---------- | :------- | :------------------------------------- | :------------ |
| `id`        | `bigint` | `PK`, `AI`                             | 主キー        |
| `menu_id`   | `bigint` | `FK (menus.id)`, `onDelete: CASCADE`   | メニュー ID   |
| `option_id` | `bigint` | `FK (options.id)`, `onDelete: CASCADE` | オプション ID |

### `bookings`

予約が行われた「事実」を記録する、原則として**不変のスナップショットテーブル**です。
予約時のメニュー名や価格などをすべて記録するため、関連するマスターデータが将来的に変更・削除されても、このテーブルの記録は影響を受けません。ステータスのような可変情報は `booking_statuses` テーブルで管理します。

| カラム名               | データ型       | 制約                                    | 説明                                                                        |
| :--------------------- | :------------- | :-------------------------------------- | :-------------------------------------------------------------------------- |
| `id`                   | `bigint`       | `PK`, `AI`                              | 主キー                                                                      |
| `shop_id`              | `bigint`       | `FK (shops.id)`, `onDelete: CASCADE`    | 店舗 ID                                                                     |
| `booker_id`            | `bigint`       | `FK (bookers.id)`, `onDelete: RESTRICT` | 予約者識別子 ID。顧客統合時もこの ID は不変。                               |
| `type`                 | `varchar(255)` | `Not NULL`                              | 予約の出自 (`guest` or `login`)。ユーザー側の予約履歴表示の出し分けに使用。 |
| `menu_id`              | `bigint`       | `Nullable`                              | メニューの ID（削除時に NULL 化）                                           |
| `menu_name`            | `varchar(255)` | `Not NULL`                              | 予約時のメニュー名（スナップショット）                                      |
| `menu_price`           | `integer`      | `Not NULL`                              | 予約時のメニュー価格（スナップショット）                                    |
| `menu_duration`        | `integer`      | `Not NULL`                              | 予約時のメニュー所要時間（スナップショット）                                |
| `requested_staff_id`   | `bigint`       | `Nullable`                              | 予約者が希望した担当者の ID（削除時に NULL 化）                             |
| `requested_staff_name` | `varchar(255)` | `Nullable`                              | 予約者が希望した担当者名（スナップショット）                                |
| `assigned_staff_id`    | `bigint`       | `Nullable`                              | 実際に割り当てられた担当者の ID（削除時に NULL 化）                         |
| `assigned_staff_name`  | `varchar(255)` | `Nullable`                              | 実際に割り当てられた担当者名（スナップショット）                            |
| `start_at`             | `datetime`     | `Not NULL`                              | 予約開始日時（タイムゾーンは`timezone`カラムを参照）                        |
| `timezone`             | `varchar(255)` | `Not NULL`                              | 予約が行われた時点の店舗のタイムゾーン（例: `Asia/Tokyo`）                  |
| `name`                 | `varchar(255)` | `Not NULL`                              | 予約者名（スナップショット）                                                |
| `email`                | `varchar(255)` | `Not NULL`                              | 予約者メールアドレス（スナップショット）                                    |
| `tel`                  | `varchar(255)` | `Not NULL`                              | 予約者電話番号（スナップショット）                                          |
| `memo`                 | `text`         | `Nullable`                              | 予約者からのメモ                                                            |
| `created_at`           | `timestamp`    |                                         | 作成日時                                                                    |
| `updated_at`           | `timestamp`    |                                         | 更新日時                                                                    |

### `booking_statuses`

予約のステータスとその変更履歴を管理します。ある予約の現在のステータスは、このテーブルで対象の`booking_id`を持つ最新のレコードとなります。

| カラム名     | データ型       | 制約                                    | 説明                                                    |
| :----------- | :------------- | :-------------------------------------- | :------------------------------------------------------ |
| `id`         | `bigint`       | `PK`, `AI`                              | 主キー                                                  |
| `booking_id` | `bigint`       | `FK (bookings.id)`, `onDelete: CASCADE` | 予約 ID                                                 |
| `status`     | `varchar(255)` | `Not NULL`                              | 予約ステータス (例: `pending`, `confirmed`, `canceled`) |
| `created_at` | `timestamp`    |                                         | ステータス変更日時                                      |
| `updated_at` | `timestamp`    |                                         | 更新日時                                                |

### `booking_option`

予約とオプションの紐付けを管理する中間テーブルです。

| カラム名     | データ型 | 制約                                    | 説明                    |
| :----------- | :------- | :-------------------------------------- | :---------------------- |
| `id`         | `bigint` | `PK`, `AI`                              | 主キー                  |
| `booking_id` | `bigint` | `FK (bookings.id)`, `onDelete: CASCADE` | 予約 ID                 |
| `option_id`  | `bigint` | `FK (options.id)`, `onDelete: CASCADE`  | 選択されたオプション ID |

### `booking_cancellation_tokens`

ゲストユーザーが予約をキャンセルするための、一時的なトークンを管理します。

| カラム名     | データ型       | 制約                                              | 説明                                         |
| :----------- | :------------- | :------------------------------------------------ | :------------------------------------------- |
| `id`         | `bigint`       | `PK`, `AI`                                        | 主キー                                       |
| `booking_id` | `bigint`       | `FK (bookings.id)`, `onDelete: CASCADE`, `Unique` | 予約 ID。予約とトークンは 1 対 1 の関係。    |
| `token`      | `varchar(255)` | `Not NULL`, `Unique`                              | キャンセル用の推測不可能なランダムトークン。 |
| `expires_at` | `timestamp`    | `Not NULL`                                        | トークンの有効期限。                         |
| `created_at` | `timestamp`    |                                                   | 作成日時                                     |
| `updated_at` | `timestamp`    |                                                   | 更新日時                                     |

### `shop_staff`

店舗と、そこに所属するスタッフ（ユーザー）を紐付けるための中間テーブル。

| カラム名         | データ型               | 制約                                 | 説明                  |
| :--------------- | :--------------------- | :----------------------------------- | :-------------------- |
| `id`             | `bigint`               | `PK`, `AI`                           | 主キー                |
| `shop_id`        | `bigint`               | `FK (shops.id)`, `onDelete: CASCADE` | 店舗 ID               |
| `user_id`        | `bigint`               | `FK (users.id)`, `onDelete: CASCADE` | スタッフのユーザー ID |
| `created_at`     | `timestamp`            |                                      | 作成日時              |
| `updated_at`     | `timestamp`            |                                      | 更新日時              |
| **ユニーク制約** | (`shop_id`, `user_id`) |                                      |                       |

### `user_shop_profiles`

ユーザーの店舗ごとのプロフィール情報（ニックネーム等）を管理します。

| カラム名         | データ型               | 制約                                 | 説明                         |
| :--------------- | :--------------------- | :----------------------------------- | :--------------------------- |
| `id`             | `bigint`               | `PK`, `AI`                           | 主キー                       |
| `user_id`        | `bigint`               | `FK (users.id)`, `onDelete: CASCADE` | ユーザー ID                  |
| `shop_id`        | `bigint`               | `FK (shops.id)`, `onDelete: CASCADE` | 店舗 ID                      |
| `nickname`       | `varchar(255)`         | `Not NULL`                           | 店舗で表示されるニックネーム |
| `contact_email`  | `varchar(255)`         | `Nullable`                           | 店舗用の連絡先メールアドレス |
| `contact_phone`  | `varchar(255)`         | `Not NULL`                           | 店舗用の連絡先電話番号       |
| `created_at`     | `timestamp`            |                                      | 作成日時                     |
| `updated_at`     | `timestamp`            |                                      | 更新日時                     |
| **ユニーク制約** | (`user_id`, `shop_id`) |                                      |                              |

### `menu_staff`

メニューと、それを施術可能なスタッフを紐付ける中間テーブル。

| カラム名         | データ型               | 制約                                 | 説明                  |
| :--------------- | :--------------------- | :----------------------------------- | :-------------------- |
| `id`             | `bigint`               | `PK`, `AI`                           | 主キー                |
| `menu_id`        | `bigint`               | `FK (menus.id)`, `onDelete: CASCADE` | メニュー ID           |
| `user_id`        | `bigint`               | `FK (users.id)`, `onDelete: CASCADE` | スタッフのユーザー ID |
| `created_at`     | `timestamp`            |                                      | 作成日時              |
| `updated_at`     | `timestamp`            |                                      | 更新日時              |
| **ユニーク制約** | (`menu_id`, `user_id`) |                                      |                       |

### `staff_schedules`

スタッフの稼働時間帯（シフト）を管理します。開始・終了時刻は `datetime` 型で保持します。

**注記:**

-   1 日に複数のレコードを登録することで、休憩時間（例：午前の稼働と午後の稼働を分ける）を表現できます。
-   時間帯の重複（オーバーラップ）チェックは、データベースの制約だけでは保証されません。アプリケーション側のロジックで、既存のスケジュールと重ならないことを確認した上で登録・更新処理を行う必要があります。

| カラム名            | データ型                                                  | 制約                                 | 説明                                 |
| :------------------ | :-------------------------------------------------------- | :----------------------------------- | :----------------------------------- |
| `id`                | `bigint`                                                  | `PK`, `AI`                           | 主キー                               |
| `shop_id`           | `bigint`                                                  | `FK (shops.id)`, `onDelete: CASCADE` | 勤務する店舗の ID                    |
| `staff_user_id`     | `bigint`                                                  | `FK (users.id)`, `onDelete: CASCADE` | スタッフのユーザー ID                |
| `workable_start_at` | `datetime`                                                | `Not NULL`                           | 稼働開始日時                         |
| `workable_end_at`   | `datetime`                                                | `Not NULL`                           | 稼働終了日時                         |
| `created_at`        | `timestamp`                                               |                                      | 作成日時                             |
| `updated_at`        | `timestamp`                                               |                                      | 更新日時                             |
| **ユニーク制約**    | (`staff_user_id`, `workable_start_at`, `workable_end_at`) |                                      | 同一スタッフ、同時間の重複登録を防止 |

### `contracts`

店舗オーナーとシステムとの契約情報を管理します。契約は店舗ではなくオーナーに紐付きます。

| カラム名     | データ型       | 制約                                 | 説明                                                                                                                                        |
| :----------- | :------------- | :----------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------ |
| `id`         | `bigint`       | `PK`, `AI`                           | 主キー                                                                                                                                      |
| `user_id`    | `bigint`       | `FK (users.id)`, `onDelete: CASCADE` | オーナーのユーザー ID                                                                                                                       |
| `max_shops`  | `integer`      | `Not NULL`, `Default: 1`             | 契約上、作成可能な店舗の上限数                                                                                                              |
| `status`     | `varchar(255)` | `Not NULL`                           | 契約ステータス (例: 'active', 'expired')                                                                                                    |
| `start_date` | `date`         | `Not NULL`                           | 契約開始日。**[注]** 現在は情報としての記録のみ。この日付に到達してもステータスは自動変更されない。                                         |
| `end_date`   | `date`         | `Not NULL`                           | 契約終了日。**[注]** 現在は情報としての記録のみ。この日付に到達してもステータスは自動変更されない。厳密な期間管理が必要な場合は改修が必要。 |
| `created_at` | `timestamp`    |                                      | 作成日時                                                                                                                                    |
| `updated_at` | `timestamp`    |                                      | 更新日時                                                                                                                                    |
