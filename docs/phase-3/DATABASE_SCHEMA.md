# データベーススキーマ定義

このドキュメントは、予約システムのデータベーススキーマを定義します。これが唯一の正しい定義（Single Source of Truth）です。

## 設計方針

-   **命名規則**: テーブル名は複数形のスネークケース（`snake_case`）、カラム名もスネークケースとします。

---

## テーブル定義

### `users`

システムに外部認証プロバイダを利用して登録されたすべてのユーザー id を管理します。ゲスト予約者は登録されません。
ユーザーのプロフィール情報（名前、連絡先等等）は、各店舗ごとに登録されるため、このテーブルでは管理しません。

| カラム名     | データ型    | 制約       | 説明              |
| :----------- | :---------- | :--------- | :---------------- |
| `id`         | `bigint`    | `PK`, `AI` | 主キー（内部 ID） |
| `created_at` | `timestamp` |            | 作成日時          |
| `updated_at` | `timestamp` |            | 更新日時          |

### `user_oauth_identities`

外部認証プロバイダ（Google, LINE 等）の識別子を管理します。

| カラム名       | データ型       | 制約                                 | 説明                        |
| :------------- | :------------- | :----------------------------------- | :-------------------------- |
| `id`           | `bigint`       | `PK`, `AI`                           | 主キー                      |
| `user_id`      | `bigint`       | `FK (users.id)`, `onDelete: CASCADE` | ユーザー ID                 |
| `provider`     | `varchar(255)` |                                      | プロバイダ名 (例: 'google') |
| `provider_sub` | `varchar(255)` |                                      | プロバイダのユーザー識別子  |
| `created_at`   | `timestamp`    |                                      | 作成日時                    |
| `updated_at`   | `timestamp`    |                                      | 更新日時                    |

**ユニーク制約** (`provider`, `provider_sub`)

### `admins`

システム管理者の情報を管理します。

| カラム名     | データ型       | 制約                                           | 説明        |
| :----------- | :------------- | :--------------------------------------------- | :---------- |
| `id`         | `bigint`       | `PK`, `AI`                                     | 主キー      |
| `user_id`    | `bigint`       | `FK (users.id)`, `Unique`, `onDelete: CASCADE` | ユーザー ID |
| `name`       | `varchar(255)` |                                                | 管理者名    |
| `created_at` | `timestamp`    |                                                | 作成日時    |
| `updated_at` | `timestamp`    |                                                | 更新日時    |

### `contract_applications`

オーナーからの契約申し込み情報を管理します。管理者はこのテーブルのレコードを見て、契約を作成するかどうかを判断します。

| カラム名        | データ型       | 制約                                 | 説明                                                   |
| :-------------- | :------------- | :----------------------------------- | :----------------------------------------------------- |
| `id`            | `bigint`       | `PK`, `AI`                           | 主キー                                                 |
| `user_id`       | `bigint`       | `FK (users.id)`, `onDelete: CASCADE` | 申し込みを行ったユーザーの ID                          |
| `customer_name` | `varchar(255)` |                                      | 申し込み時に入力されたお客様名称                       |
| `email`         | `varchar(255)` |                                      | 申し込みを行ったユーザーのメールアドレス。             |
| `status`        | `varchar(255)` | `Default: 'pending'`                 | 申し込みの状態 (例: `pending`, `approved`, `rejected`) |
| `created_at`    | `timestamp`    |                                      | 作成日時                                               |
| `updated_at`    | `timestamp`    |                                      | 更新日時                                               |

### `contracts`

オーナーとシステムとの契約情報を管理します。

| カラム名         | データ型       | 制約                                                              | 説明                                                                                       |
| :--------------- | :------------- | :---------------------------------------------------------------- | :----------------------------------------------------------------------------------------- |
| `id`             | `bigint`       | `PK`, `AI`                                                        | 主キー                                                                                     |
| `user_id`        | `bigint`       | `FK (users.id)`, `Unique`, `onDelete: CASCADE`                    | オーナーのユーザー ID                                                                      |
| `name`           | `varchar(255)` |                                                                   | 管理者が識別するための契約名 (例: 「株式会社〇〇様 スタンダードプラン」)                   |
| `application_id` | `bigint`       | `FK (contract_applications.id)`, `Nullable`, `onDelete: SET NULL` | 契約申し込みとの紐づけ情報（申し込みフォーム以外からの申し込みも考慮）                     |
| `max_shops`      | `integer`      | `Default: 1`                                                      | 契約上、作成可能な店舗の上限数                                                             |
| `status`         | `varchar(255)` | `Default: 'active'`                                               | 契約ステータス ('active', 'expired') 。active な契約を持つ user_id は owner であるとみなす |
| `start_date`     | `date`         |                                                                   | 契約開始日。現在は情報としての記録のみ。                                                   |
| `end_date`       | `date`         |                                                                   | 契約終了日。現在は情報としての記録のみ。ステータスは自動変更されない。                     |
| `created_at`     | `timestamp`    |                                                                   | 作成日時                                                                                   |
| `updated_at`     | `timestamp`    |                                                                   | 更新日時                                                                                   |

### `shop_staff_applications`

店舗スタッフへ登録するための申し込み情報を管理します。オーナーはこのテーブルのレコードを見て、店舗スタッフに登録するかどうかを判断します。

| カラム名     | データ型       | 制約                                 | 説明                                                   |
| :----------- | :------------- | :----------------------------------- | :----------------------------------------------------- |
| `id`         | `bigint`       | `PK`, `AI`                           | 主キー                                                 |
| `shop_id`    | `bigint`       | `FK (shops.id)`, `onDelete: CASCADE` | 店舗 ID                                                |
| `user_id`    | `bigint`       | `FK (users.id)`, `onDelete: CASCADE` | 申し込みを行ったユーザーの ID                          |
| `name`       | `varchar(255)` |                                      | 申し込み時に入力されたユーザーの名前                   |
| `status`     | `varchar(255)` | `Default: 'pending'`                 | 申し込みの状態 (例: `pending`, `approved`, `rejected`) |
| `created_at` | `timestamp`    |                                      | 作成日時                                               |
| `updated_at` | `timestamp`    |                                      | 更新日時                                               |

### `shop_staffs`

店舗のスタッフ登録を管理します。
user_id が NULL の場合、そのスタッフはオーナーのみが操作可能とします。

| カラム名     | データ型    | 制約                                             | 説明                  |
| :----------- | :---------- | :----------------------------------------------- | :-------------------- |
| `id`         | `bigint`    | `PK`, `AI`                                       | 主キー                |
| `shop_id`    | `bigint`    | `FK (shops.id)`, `onDelete: CASCADE`             | 店舗 ID               |
| `user_id`    | `bigint`    | `FK (users.id)`, `Nullable`, `onDelete: CASCADE` | スタッフのユーザー ID |
| `created_at` | `timestamp` |                                                  | 作成日時              |
| `updated_at` | `timestamp` |                                                  | 更新日時              |

**ユニーク制約** (`shop_id`, `user_id`)

### `shop_staff_profiles`

店舗スタッフのプロフィール情報を管理します。

| カラム名          | データ型       | 制約                                                 | 説明                                     |
| :---------------- | :------------- | :--------------------------------------------------- | :--------------------------------------- |
| `id`              | `bigint`       | `PK`, `AI`                                           | 主キー                                   |
| `shop_staff_id`   | `bigint`       | `FK (shop_staffs.id)`, `Unique`, `onDelete: CASCADE` | 店舗スタッフ ID                          |
| `nickname`        | `varchar(255)` |                                                      | 店舗で表示されるニックネーム             |
| `small_image_url` | `varchar(255)` | `Nullable`                                           | 店舗で表示される画像（小）のファイルパス |
| `large_image_url` | `varchar(255)` | `Nullable`                                           | 店舗で表示される画像（大）のファイルパス |
| `created_at`      | `timestamp`    |                                                      | 作成日時                                 |
| `updated_at`      | `timestamp`    |                                                      | 更新日時                                 |

### `shop_staff_schedules`

スタッフの稼働時間を管理します。予約システムは、このテーブルの情報を基に、予約可能枠を計算します。

-   1 日に複数のレコードを登録することで、休憩時間（例：午前の稼働と午後の稼働を分ける）を表現できます。
-   時間帯の一部重複（オーバーラップ）チェックは、データベースの制約では保証しません。
-   アプリケーション側で、既存のスケジュールと重ならないことを確認した上で登録・更新処理を行う必要があります。
-   オーナーやスタッフが、スタッフの稼働時間外に予約を入れようとした場合に、ワーニング表示します。あくまでワーニング表示のために利用し、予約登録は可能とします。
-   **休日として設定された日は、`workable_start_at` と `workable_end_at` に同じ日時（例: `YYYY-MM-DD 00:00:00`）を設定したレコードを 1 件登録することで表現します。レコードが 1 件も存在しない日は「未設定」状態として扱います。**

| カラム名            | データ型    | 制約                                       | 説明            |
| :------------------ | :---------- | :----------------------------------------- | :-------------- |
| `id`                | `bigint`    | `PK`, `AI`                                 | 主キー          |
| `shop_staff_id`     | `bigint`    | `FK (shop_staffs.id)`, `onDelete: CASCADE` | 店舗スタッフ ID |
| `workable_start_at` | `datetime`  |                                            | 稼働開始日時    |
| `workable_end_at`   | `datetime`  |                                            | 稼働終了日時    |
| `created_at`        | `timestamp` |                                            | 作成日時        |
| `updated_at`        | `timestamp` |                                            | 更新日時        |

**ユニーク制約** (`shop_staff_id`, `workable_start_at`, `workable_end_at`) 同一スタッフ、同時間の重複登録を防止

### `shops`

店舗の基本情報を管理します。

| カラム名                        | データ型       | 制約                                 | 説明                                                                                                              |
| :------------------------------ | :------------- | :----------------------------------- | :---------------------------------------------------------------------------------------------------------------- |
| `id`                            | `bigint`       | `PK`, `AI`                           | 主キー                                                                                                            |
| `owner_user_id`                 | `bigint`       | `FK (users.id)`, `onDelete: CASCADE` | この店舗を所有するオーナーのユーザー。この user は、この店舗のオーナーであるとみなされる。                        |
| `slug`                          | `varchar(255)` | `Unique`                             | URL で利用する、URL セーフな一意の識別子 (例: 'store-1')。ただし、`/` は含まれない。                              |
| `name`                          | `varchar(255)` |                                      | 店舗名                                                                                                            |
| `time_slot_interval`            | `integer`      | `Default: 30`                        | 予約枠の表示単位（分）                                                                                            |
| `cancellation_deadline_minutes` | `integer`      | `Default: 1440`                      | キャンセル可能な期限（分単位）                                                                                    |
| `booking_deadline_minutes`      | `integer`      | `Default: 0`                         | 店舗の基本予約締め切り（分単位）。0 は直前まで可。                                                                |
| `booking_confirmation_type`     | `varchar(255)` | `Default: 'automatic'`               | 予約承認方法 (`automatic` or `manual`)                                                                            |
| `accepts_online_bookings`       | `boolean`      | `Default: true`                      | オンライン予約を受け付けフラグ。`false`の場合、顧客向けの予約フォームは無効化されるが、管理画面からの予約は可能。 |
| `timezone`                      | `varchar(255)` | `Default: 'Asia/Tokyo'`              | 店舗のタイムゾーン。現在は日本の店舗を想定。デフォルト値は `'Asia/Tokyo'` 。                                      |
| `email`                         | `varchar(255)` |                                      | 店舗のメールアドレス。現在 gmail のみ想定。メール送信機能対応のため。                                             |
| `created_at`                    | `timestamp`    |                                      | 作成日時                                                                                                          |
| `updated_at`                    | `timestamp`    |                                      | 更新日時                                                                                                          |

### `shop_business_hours_regulars`

店舗営業時間を定義します。店舗スタッフが自身の稼働時間を登録する際のチェックに利用します。例えば、このテーブルで月曜日が「09:00-18:00」と設定されている場合、スタッフは月曜日に「08:00」から開始したり、「19:00」に終了するシフトを登録しようとすると、ワーニングを表示します。この設定は、あくまでスタッフのシフト登録画面でのバリデーションにのみ使用され、顧客向けの予約可能枠の計算には一切影響しません。

| カラム名      | データ型    | 制約                                | 説明                           |
| :------------ | :---------- | :---------------------------------- | :----------------------------- |
| `id`          | `bigint`    | `PK`, `AI`                          | 主キー                         |
| `shop_id`     | `bigint`    | `FK(shops.id)`, `onDelete: CASCADE` | 店舗 ID                        |
| `day_of_week` | `integer`   |                                     | 曜日 (0=日曜, 1=月曜...)       |
| `start_time`  | `time`      | `Nullable`                          | 営業開始時刻                   |
| `end_time`    | `time`      | `Nullable`                          | 営業終了時刻                   |
| `is_open`     | `boolean`   | `Default: true`                     | 通常営業日。false の場合定休日 |
| `created_at`  | `timestamp` |                                     | 作成日時                       |
| `updated_at`  | `timestamp` |                                     | 更新日時                       |

**ユニーク制約** (`shop_id`, `day_of_week`)

### `shop_special_open_days`

店舗の特別営業日を管理します。

| カラム名     | データ型    | 制約                                 | 説明                            |
| :----------- | :---------- | :----------------------------------- | :------------------------------ |
| `id`         | `bigint`    | `PK`, `AI`                           | 主キー                          |
| `shop_id`    | `bigint`    | `FK (shops.id)`, `onDelete: CASCADE` | 店舗 ID                         |
| `date`       | `date`      |                                      | 特別営業日                      |
| `start_time` | `time`      |                                      | 営業開始時刻                    |
| `end_time`   | `time`      |                                      | 営業終了時刻                    |
| `name`       | `text`      | `Nullable`                           | 営業日の名前 (例: 「祝日営業」) |
| `created_at` | `timestamp` |                                      | 作成日時                        |
| `updated_at` | `timestamp` |                                      | 更新日時                        |

### `shop_special_closed_days`

店舗の特別休業日を管理します。

| カラム名     | データ型    | 制約                                 | 説明                              |
| :----------- | :---------- | :----------------------------------- | :-------------------------------- |
| `id`         | `bigint`    | `PK`, `AI`                           | 主キー                            |
| `shop_id`    | `bigint`    | `FK (shops.id)`, `onDelete: CASCADE` | 店舗 ID                           |
| `start_at`   | `date`      |                                      | 休日開始日                        |
| `end_at`     | `date`      |                                      | 休日終了日                        |
| `name`       | `text`      | `Nullable`                           | 休日の名前 (例: 「年末年始休暇」) |
| `created_at` | `timestamp` |                                      | 作成日時                          |
| `updated_at` | `timestamp` |                                      | 更新日時                          |

### `shop_menus`

各店舗が提供するメニュー情報を管理します。

| カラム名                         | データ型       | 制約                                 | 説明                                                                              |
| :------------------------------- | :------------- | :----------------------------------- | :-------------------------------------------------------------------------------- |
| `id`                             | `bigint`       | `PK`, `AI`                           | 主キー                                                                            |
| `shop_id`                        | `bigint`       | `FK (shops.id)`, `onDelete: CASCADE` | 店舗 ID                                                                           |
| `name`                           | `varchar(255)` |                                      | メニュー名                                                                        |
| `price`                          | `integer`      | `Default: 0`                         | 価格                                                                              |
| `description`                    | `text`         | `Nullable`                           | メニューの説明                                                                    |
| `duration`                       | `integer`      | `Default: 0`                         | 所要時間（分）                                                                    |
| `requires_cancellation_deadline` | `boolean`      | `Default: false`                     | 特別なキャンセル期限が必要かどうかのフラグ。`false`なら店舗の設定が適用される。   |
| `cancellation_deadline_minutes`  | `integer`      | `Default: 0`                         | キャンセル可能な期限（分単位）。                                                  |
| `requires_booking_deadline`      | `boolean`      | `Default: false`                     | 特別な予約締め切りが必要かどうかのフラグ。`false`なら店舗の設定が適用される。     |
| `booking_deadline_minutes`       | `integer`      | `Default: 0`                         | メニュー固有の予約締め切り（分単位）。                                            |
| `requires_staff_assignment`      | `boolean`      | `Default: false`                     | 担当者の割り当てが必須かどうかのフラグ。`false`なら不要（＝どのスタッフでも可）。 |
| `created_at`                     | `timestamp`    |                                      | 作成日時                                                                          |
| `updated_at`                     | `timestamp`    |                                      | 更新日時                                                                          |

### `shop_options`

各店舗が提供するオプションメニューの情報を管理します。

| カラム名              | データ型       | 制約                                 | 説明                                                   |
| :-------------------- | :------------- | :----------------------------------- | :----------------------------------------------------- |
| `id`                  | `bigint`       | `PK`, `AI`                           | 主キー                                                 |
| `shop_id`             | `bigint`       | `FK (shops.id)`, `onDelete: CASCADE` | 店舗 ID                                                |
| `name`                | `varchar(255)` |                                      | オプション名 (例: 「炭酸泉シャンプー」)                |
| `price`               | `integer`      | `Default: 0`                         | 追加料金                                               |
| `additional_duration` | `integer`      | `Default: 0`                         | 追加の所要時間（分）。メニューの所要時間に加算される。 |
| `description`         | `text`         | `Nullable`                           | オプションの説明                                       |
| `created_at`          | `timestamp`    |                                      | 作成日時                                               |
| `updated_at`          | `timestamp`    |                                      | 更新日時                                               |

### `shop_menu_options`

メニューに対して、選択可能なオプションを管理します。ここにレコードが存在することで、その組み合わせが許可されていることを示します。

| カラム名         | データ型    | 制約                                        | 説明          |
| :--------------- | :---------- | :------------------------------------------ | :------------ |
| `id`             | `bigint`    | `PK`, `AI`                                  | 主キー        |
| `shop_menu_id`   | `bigint`    | `FK (shop_menus.id)`, `onDelete: CASCADE`   | メニュー ID   |
| `shop_option_id` | `bigint`    | `FK (shop_options.id)`, `onDelete: CASCADE` | オプション ID |
| `created_at`     | `timestamp` |                                             | 作成日時      |
| `updated_at`     | `timestamp` |                                             | 更新日時      |

### `shop_menu_staffs`

メニューと、それを施術可能なスタッフを管理する。

| カラム名        | データ型    | 制約                                       | 説明            |
| :-------------- | :---------- | :----------------------------------------- | :-------------- |
| `id`            | `bigint`    | `PK`, `AI`                                 | 主キー          |
| `shop_menu_id`  | `bigint`    | `FK (shop_menus.id)`, `onDelete: CASCADE`  | メニュー ID     |
| `shop_staff_id` | `bigint`    | `FK (shop_staffs.id)`, `onDelete: CASCADE` | 店舗スタッフ ID |
| `created_at`    | `timestamp` |                                            | 作成日時        |
| `updated_at`    | `timestamp` |                                            | 更新日時        |

**ユニーク制約** (`shop_menu_id`, `shop_staff_id`)

### `shop_bookers`

店舗の予約者情報を管理します。予約者情報は店舗ごとに異なる場合があるため、各店舗ごとに管理します。

| カラム名           | データ型       | 制約                                             | 説明                                                     |
| :----------------- | :------------- | :----------------------------------------------- | :------------------------------------------------------- |
| `id`               | `bigint`       | `PK`, `AI`                                       | 予約者識別子 ID                                          |
| `shop_id`          | `bigint`       | `FK (shops.id)`, `onDelete: CASCADE`             | 店舗 ID                                                  |
| `user_id`          | `bigint`       | `FK (users.id)`, `Nullable`, `onDelete: CASCADE` | ユーザー ID。Google や LINE でのログインに生成されたもの |
| `number`           | `integer`      | `Unique`                                         | 会員番号。予約者が予約を見るときの path としても利用     |
| `name`             | `varchar(255)` |                                                  | 表示される予約者の名前、予約者側で設定・変更するもの     |
| `contact_email`    | `varchar(255)` |                                                  | 予約者の連絡先メールアドレス                             |
| `contact_phone`    | `varchar(255)` |                                                  | 予約者の連絡先電話番号                                   |
| `note_from_booker` | `text`         | `Nullable`                                       | 予約者からのメモ。予約者側で設定・変更するもの           |
| `created_at`       | `timestamp`    |                                                  | 作成日時                                                 |
| `updated_at`       | `timestamp`    |                                                  | 更新日時                                                 |

**ユニーク制約** (`shop_id`, `user_id`)

### `shop_bookers_crm`

店舗予約者の店舗側のみに見える追加情報を管理します。予約者には表示されません。

| カラム名          | データ型       | 制約                                                  | 説明                                         |
| :---------------- | :------------- | :---------------------------------------------------- | :------------------------------------------- |
| `id`              | `bigint`       | `PK`, `AI`                                            | 主キー                                       |
| `shop_bookers_id` | `bigint`       | `FK (shop_bookers.id)`, `onDelete: CASCADE`, `Unique` | 予約者識別子 ID                              |
| `name_kana`       | `varchar(255)` | `Nullable`                                            | 予約者のよみがな、店舗側だけで設定・変更可能 |
| `shop_memo`       | `text`         | `Nullable`                                            | 予約者の店舗側メモ                           |
| `last_booking_at` | `timestamp`    | `Nullable`                                            | 最終予約日時                                 |
| `booking_count`   | `integer`      | `Nullable`, `default: 0`                              | 予約回数                                     |
| `created_at`      | `timestamp`    |                                                       | 作成日時                                     |
| `updated_at`      | `timestamp`    |                                                       | 更新日時                                     |

### `bookings`

予約情報を管理します。予約時のメニュー名や価格などをすべて記録し、関連するマスターデータが将来的に変更・削除されても、このテーブルの記録は影響を受けません。

| カラム名               | データ型       | 制約                                                   | 説明                                                       |
| :--------------------- | :------------- | :----------------------------------------------------- | :--------------------------------------------------------- |
| `id`                   | `bigint`       | `PK`, `AI`                                             | 主キー                                                     |
| `shop_id`              | `bigint`       | `FK (shops.id)`, `onDelete: CASCADE`                   | 店舗 ID                                                    |
| `shop_booker_id`       | `bigint`       | `FK (shop_bookers.id)`, `Nullable`                     | 予約者識別子 ID。`null` の場合はゲスト予約として扱う       |
| `status`               | `varchar(255)` | `Default: 'pending'`                                   | 予約ステータス (例: `pending`, `confirmed`, `cancelled`)   |
| `menu_id`              | `bigint`       | `FK (shop_menus.id)`, `Nullable`, `onDelete: SET NULL` | メニューの ID（スナップショット）                          |
| `menu_name`            | `varchar(255)` |                                                        | 予約時のメニュー名（スナップショット）                     |
| `menu_price`           | `integer`      |                                                        | 予約時のメニュー価格（スナップショット）                   |
| `menu_duration`        | `integer`      |                                                        | 予約時のメニュー所要時間（スナップショット）               |
| `requested_staff_id`   | `bigint`       | `FK (shop_staffs.id)`, `Nullable`                      | 予約者が希望した担当者の ID（スナップショット）            |
| `requested_staff_name` | `varchar(255)` | `Nullable`                                             | 予約者が希望した担当者名（スナップショット）               |
| `assigned_staff_id`    | `bigint`       | `FK (shop_staffs.id)`, `Nullable`                      | 実際に割り当てられた担当者の ID（スナップショット）        |
| `assigned_staff_name`  | `varchar(255)` | `Nullable`                                             | 実際に割り当てられた担当者名（スナップショット）           |
| `timezone`             | `varchar(255)` |                                                        | 予約が行われた時点の店舗のタイムゾーン（例: `Asia/Tokyo`） |
| `start_at`             | `datetime`     |                                                        | 予約開始日時（タイムゾーンは`timezone`カラムを参照）       |
| `end_at`               | `datetime`     |                                                        | 予約終了日時（タイムゾーンは`timezone`カラムを参照）       |
| `booker_name`          | `varchar(255)` |                                                        | 予約者名（スナップショット）                               |
| `contact_email`        | `varchar(255)` |                                                        | 予約者メールアドレス（スナップショット）                   |
| `contact_phone`        | `varchar(255)` |                                                        | 予約者電話番号（スナップショット）                         |
| `note_from_booker`     | `text`         | `Nullable`                                             | 予約者からのメモ。予約者、および店舗側が参照可能           |
| `shop_memo`            | `text`         | `Nullable`                                             | 予約者の店舗側メモ。予約者には表示されない                 |
| `booking_channel`      | `varchar(255)` | `Not NULL`, `Default: 'web'`                           | 予約経路 (例: `web`, `line`, `instagram`)                  |
| `created_at`           | `timestamp`    |                                                        | 作成日時                                                   |
| `updated_at`           | `timestamp`    |                                                        | 更新日時                                                   |

### `booking_options`

予約とオプションの紐付けを管理する。options マスターデータが将来的に変更・削除されても、このテーブルの記録は影響を受けません。

| カラム名          | データ型       | 制約                                                     | 説明                                                 |
| :---------------- | :------------- | :------------------------------------------------------- | :--------------------------------------------------- |
| `id`              | `bigint`       | `PK`, `AI`                                               | 主キー                                               |
| `booking_id`      | `bigint`       | `FK (bookings.id)`, `onDelete: CASCADE`                  | 予約 ID                                              |
| `option_id`       | `bigint`       | `FK (shop_options.id)`, `Nullable`, `onDelete: SET NULL` | 予約時の選択されたオプション ID（スナップショット）  |
| `option_name`     | `varchar(255)` |                                                          | 予約時の選択されたオプション名（スナップショット）   |
| `option_price`    | `integer`      |                                                          | 予約時の選択されたオプション価格（スナップショット） |
| `option_duration` | `integer`      |                                                          | 予約時の選択されたオプション時間（スナップショット） |
| `created_at`      | `timestamp`    |                                                          | 作成日時                                             |
| `updated_at`      | `timestamp`    |                                                          | 更新日時                                             |

### `booking_cancellation_tokens`

ゲストユーザーが予約をキャンセルするための、一時的なトークンを管理します。

| カラム名     | データ型       | 制約                                              | 説明                                         |
| :----------- | :------------- | :------------------------------------------------ | :------------------------------------------- |
| `id`         | `bigint`       | `PK`, `AI`                                        | 主キー                                       |
| `booking_id` | `bigint`       | `FK (bookings.id)`, `Unique`, `onDelete: CASCADE` | 予約 ID。予約とトークンは 1 対 1 の関係。    |
| `token`      | `varchar(255)` | `Unique`                                          | キャンセル用の推測不可能なランダムトークン。 |
| `expires_at` | `timestamp`    |                                                   | トークンの有効期限。                         |
| `created_at` | `timestamp`    |                                                   | 作成日時                                     |
| `updated_at` | `timestamp`    |                                                   | 更新日時                                     |
