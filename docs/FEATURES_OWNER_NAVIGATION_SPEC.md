# オーナー向けナビゲーションメニュー仕様書

## 1. 目的

オーナー向け管理画面に共通のナビゲーションメニューを提供し、各機能への遷移を容易にします。

## 2. レイアウト仕様

### PC版 (幅768px以上)

- 左側に固定サイドバー (幅 220px)
- コンテンツは右側に配置
- サイドバー上部に店舗名を表示
- メニュー項目はカテゴリごとにグループ化

### モバイル版 (幅767px以下)

- 上部にアプリバー (店舗名 + ハンバーガーメニュー)
- ハンバーガーメニュータップでドロワー表示
- パンくずリストは省略

## 3. メニュー構成

### 店舗別メニュー

| カテゴリ | メニュー項目 | 遷移先 | アイコン |
|:---|:---|:---|:---|
| - | 店舗トップ | /owner/shops/{shop} | mdi-home |
| 予約 | 予約一覧 | /owner/shops/{shop}/bookings | mdi-calendar |
| 顧客 | 予約者一覧 | /owner/shops/{shop}/bookers | mdi-account-group |
| スタッフ | スタッフ一覧 | /owner/shops/{shop}/staffs | mdi-account-tie |
| スタッフ | シフト管理 | /owner/shops/{shop}/shifts | mdi-clock-outline |
| スタッフ | スタッフ申請 | /owner/shops/{shop}/staff-applications | mdi-account-plus |
| メニュー | メニュー一覧 | /owner/shops/{shop}/menus | mdi-food |
| メニュー | オプション一覧 | /owner/shops/{shop}/options | mdi-plus-circle-outline |
| 設定 | 店舗情報 | /owner/shops/{shop}/edit | mdi-store-edit |
| 設定 | 営業時間 | /owner/shops/{shop}/business-hours | mdi-clock |

### フッター

- 店舗一覧へ戻る: /owner/shops

## 4. 技術仕様

### 使用コンポーネント

- Vuetify: v-navigation-drawer, v-app-bar, v-list

### ファイル構成

- resources/js/components/owner/OwnerLayout.vue: 共通レイアウト
- 各画面で OwnerLayout をラップして使用

### Props

| 名前 | 型 | 説明 |
|:---|:---|:---|
| shop | Object | 店舗情報 (name, slug) |
| currentPage | string | 現在のページ識別子 (ハイライト用) |
