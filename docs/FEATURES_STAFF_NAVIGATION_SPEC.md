# スタッフ向けナビゲーションメニュー仕様書

## 1. 目的

スタッフ向け管理画面に共通のナビゲーションメニューを提供し、各業務機能への遷移を容易にします。オーナー向け画面 (`OwnerLayout`) と統一感のあるデザインを採用します。

## 2. レイアウト仕様

### PC版 (幅768px以上)

- 左側に固定サイドバー (幅 220px)
- コンテンツは右側に配置
- サイドバー上部に店舗名を表示
- メニュー項目はカテゴリごとにグループ化

### モバイル版 (幅767px以下)

- 上部にアプリバー (店舗名 + ハンバーガーメニュー)
- ハンバーガーメニュータップでドロワー表示

## 3. メニュー構成

### 店舗別メニュー

| カテゴリ | メニュー項目 | 遷移先 | アイコン | ノート |
|:---|:---|:---|:---|:---|
| 予約 | 予約一覧 | /shops/{shop}/staff/bookings | mdi-calendar | |
| 顧客 | 予約者一覧 | /shops/{shop}/staff/bookers | mdi-account-group | |
| 業務 | シフト確認 | /shops/{shop}/staff/shifts | mdi-clock-outline | 自身のシフト |
| 業務 | スタッフ一覧 | /shops/{shop}/staff/staffs | mdi-account-tie | 同僚確認 |
| 設定 | プロフィール | /shops/{shop}/staff/profile | mdi-account-cog | 自身の情報 |

### 共通アクション

- ログアウト: (サイドバー最下部またはアプリバー内)

## 4. 技術仕様

### 使用コンポーネント

- Vuetify: `v-navigation-drawer`, `v-app-bar`, `v-list`
- `OwnerLayout.vue` をベースに `StaffLayout.vue` を作成

### ファイル構成

- `resources/js/components/staff/StaffLayout.vue`: 共通レイアウトコンポーネント

### Props

| 名前 | 型 | 説明 |
|:---|:---|:---|
| `shop` | `Object` | 店舗情報 (name, slug) |
| `staff` | `Object` | ログイン中のスタッフ情報 (nickname, icon等) ※必要であれば |
| `currentPage` | `string` | 現在のページ識別子 (ハイライト用) |

### 適用対象画面

以下のディレクトリ配下の各ページコンポーネントに `StaffLayout` を適用します。

- `resources/js/staff/bookings/`
- `resources/js/staff/bookers/`
- `resources/js/staff/shifts/`
- `resources/js/staff/staffs/` (一覧画面)
- `resources/js/staff/profile/` (または `StaffController@edit` に対応するビュー)

## 5. デザイン要件

- テーマカラー: スタッフ画面用のテーマカラー（もしあれば）またはデフォルト
- レスポンシブ対応: `useDisplay` コンポーザブルを利用して PC/SP の表示切り替えを行う
