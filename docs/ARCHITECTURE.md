# ARCHITECTURE.md

## 1. 目的と運用ルール

### 1.1. 目的
この文書は予約サービスの **システムアーキテクチャ** の全体像と設計思想を定義し、開発・運用における共通認識を持つことを目的とします。

### 1.2. 本書の役割
本書は、システム全体のアーキテクチャの概要を示し、より詳細な仕様を記述した `docs/` 配下の他のドキュメントへの **ハブ（索引）** として機能します。アーキテクチャに関する主要な決定事項や全体像を把握したい場合は、まずこのドキュメントを参照してください。

### 1.3. 運用ルール
`docs/` 配下のドキュメントを新規作成または更新する際は、 **必ず本書との整合性を確認してください。** 具体的には、関連するセクションから適切にリンクを張る、あるいは既存のリンクが最新の状態を反映しているかを確認する作業を必須とします。

---

## 2. 関連ドキュメント一覧

-   **認可設計**: [AUTHORIZATION_SPEC.md](./AUTHORIZATION_SPEC.md)
-   **データベース設計**: [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md)
-   **予約フォーム仕様**: [BOOKING_FORM_SPEC.md](./BOOKING_FORM_SPEC.md)
-   **予約管理機能**: [RESERVATION_MANAGEMENT_SPEC.md](./RESERVATION_MANAGEMENT_SPEC.md)
-   **空き時間計算ロジック**: [AVAILABILITY_CALCULATION_LOGIC.md](./AVAILABILITY_CALCULATION_LOGIC.md)
-   **手動予約時のバリデーション**: [MANUAL_BOOKING_VALIDATION_LOGIC.md](./MANUAL_BOOKING_VALIDATION_LOGIC.md)
-   **オーナー向け機能**:
    -   オンボーディングと契約: [OWNER_ONBOARDING_AND_CONTRACT_SPEC.md](./OWNER_ONBOARDING_AND_CONTRACT_SPEC.md)
    -   ワークフロー: [OWNER_ONBOARDING_WORKFLOW.md](./OWNER_ONBOARDING_WORKFLOW.md)
-   **店舗管理機能**: [SHOP_MANAGEMENT_SPEC.md](./SHOP_MANAGEMENT_SPEC.md)
-   **スタッフ向け予約管理機能**: [STAFF_RESERVATION_MANAGEMENT_SPEC.md](./STAFF_RESERVATION_MANAGEMENT_SPEC.md)
-   **管理者向け機能**: [ADMIN_FEATURES_SPEC.md](./ADMIN_FEATURES_SPEC.md)
-   **テスト・データ**:
    -   テストガイド: [TESTING_GUIDE.md](./TESTING_GUIDE.md)
    -   ワークフローテスト: [WORKFLOW_TESTS.md](./WORKFLOW_TESTS.md)
    -   データセットアップ仕様: [DATA_SETUP_SPEC.md](./DATA_SETUP_SPEC.md)

---

## 3. アーキテクチャ方針

-   **フロントエンド**: Laravel + Blade (MPA) を基本とし、一部 Vue 3 + Vuetify + TypeScript による動的 UI
-   **バックエンド**: Laravel (API 提供 + 認証・セッション管理)
-   **データベース**: 単一 DB + `shop_id` スコープ管理（将来的に DB 分離型マルチテナントへ拡張可能）
-   **責務分担**:
    -   Laravel + Blade: ページ遷移・認証・セッション・CSRF管理・フォーム処理
    -   Vue + Vuetify: 予約フォームUI、予約一覧 (フィルタ/ソート/ページネーション)
-   **API**: Laravel コントローラから JSON を返却、Vue が描画更新

---

## 4. マルチテナントのデータ分離戦略

オーナーやスタッフが自身の所属する店舗の情報のみを閲覧・操作できるよう、グローバルスコープとPolicyを組み合わせてデータ分離を堅牢に実現する。

-   **グローバルスコープ**: `shop_id` による自動的なクエリ絞り込みを行い、意図しない情報漏洩をシステムレベルで防止する。
-   **Policyによる認可**: 個別のデータに対する操作権限を厳密に検証し、不正アクセスを防ぐ。

---

## 5. データモデルに関する重要方針

詳細なデータモデルは [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md) を参照。

### 5.1. 顧客アカウントモデルと統合ロジック
ゲスト予約と会員登録ユーザーを柔軟に紐付けるため、「マスターアカウント (`users`)」「来店カード (`bookers`)」「予約票 (`bookings`)」の3層モデルを採用する。これにより、予約情報を変更することなく顧客の名寄せ（統合）を可能にする。

### 5.2. データ削除方針
ユーザー退会やメニュー廃止など、マスターデータは **物理削除** を前提とする。削除時はモデルイベントを利用して関連する予約情報 (`bookings`) の外部キーを `NULL` に更新し、参照整合性とデータの匿名性を担保する。

---

## 6. API 設計方針

各ロール（Booker, Staff, Owner, Admin）に応じたエンドポイントを定義する。

### 6.1. Booker（予約者向け）
-   `/shops/{shop_slug}/bookings` (一覧, 作成, 詳細, 変更, キャンセル)

### 6.2. Staff（店舗スタッフ向け）
-   `/shops/{shop_slug}/staff/...` (ダッシュボード, 予約管理, シフト管理)

### 6.3. Owner（店舗オーナー向け）
-   `/owner/...` (ダッシュボード, 店舗管理, スタッフ管理, 契約管理)

### 6.4. Admin（全体管理者向け）
-   `/admin/...` (オーナー管理, 契約管理)

---

## 7. ディレクトリ構成

-   `app/Http/Controllers/{Role}/`
-   `app/Services/`
-   `app/Policies/`
-   `resources/views/{role}/`