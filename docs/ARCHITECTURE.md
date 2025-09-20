# ARCHITECTURE.md

## 1. 目的と運用ルール

### 1.1. 目的
この文書は予約サービスの **システムアーキテクチャ** の全体像と設計思想を定義し、開発・運用における共通認識を持つことを目的とします。

### 1.2. 本書の役割
本書は、システム全体のアーキテクチャの概要を示し、より詳細な仕様を記述した `docs/` 配下の他のドキュメントへの **ハブ（索引）** として機能します。アーキテクチャに関する主要な決定事項や全体像を把握したい場合は、まずこのドキュメントを参照してください。

### 1.3. 運用ルール
`docs/` 配下のドキュメントを新規作成または更新する際は、 **必ず本書との整合性を確認してください。** 具体的には、関連するセクションから適切にリンクを張る、あるいは既存のリンクが最新の状態を反映しているかを確認する作業を必須とします。

---

## 2. アーキテクチャ方針

-   **フロントエンド**: Laravel + Blade (MPA) を基本とし、一部 Vue 3 + Vuetify + TypeScript による動的 UI
-   **バックエンド**: Laravel (API 提供 + 認証・セッション管理)
-   **データベース**: 単一 DB + `shop_id` スコープ管理（将来的に DB 分離型マルチテナントへ拡張可能）
-   **責務分担**:
    -   Laravel + Blade: ページ遷移・認証・セッション・CSRF管理・フォーム処理
    -   Vue + Vuetify: 予約フォームUI、予約一覧 (フィルタ/ソート/ページネーション)
-   **API**: Laravel コントローラから JSON を返却、Vue が描画更新

### 2.1. BladeとVueの連携方式
本プロジェクトでは、Laravel BladeとVueコンポーネントを以下の方式で連携させることを原則とします。

1.  **Bladeテンプレート**:
    -   コントローラから渡されたデータを `json_encode` し、`id="app"` を持つ単一のdiv要素の `data-props` 属性にセットします。
    -   どのVueコンポーネントを呼び出すかを識別するため、`data-page` 属性も併せて指定します。
    -   Bladeテンプレート内に直接的なVueコンポーネントの記述やロジックは含めません。

    ```html
    <!-- 例: resources/views/admin/users/index.blade.php -->
    @php
    $props = ['users' => $users];
    @endphp
    <div
      id="app"
      data-page="admin-users-index"
      data-props="{{ json_encode($props) }}"
    ></div>
    ```

2.  **Vueコンポーネントのマウント**:
    -   `resources/js/app.ts` が `id="app"` のdiv要素を検知します。
    -   `data-page` 属性の値をキーとして、対応するVueコンポーネントを選択します。
    -   `data-props` 属性のJSON文字列をパースし、コンポーネントのプロパティとして渡してマウントします。

このアーキテクチャにより、サーバーサイド（Laravel）とフロントエンド（Vue）の関心を明確に分離し、見通しの良い開発を維持します。

### 2.2. 一覧画面における動的フィルタリングの実装方針

検索機能を持つすべての一覧画面は、UXと開発効率を両立するため、以下のハイブリッド方針で実装することを原則とします。

-   **目的**: ページ全体のリロードなしに快適なフィルタリング操作を提供しつつ、ブラウザの「戻る」機能やURLの共有を可能にする。
-   **基本方針**: 初回読み込みはLaravel(サーバーサイド)で行い、フィルタリングやページネーションなどの動的な操作はVue(クライアントサイド)が担当する。

#### 実装フロー

1.  **初回アクセス時 (またはブラウザバック時)**
    -   ユーザーが `GET /admin/users?search=...` のようなURLにアクセスします。
    -   Laravelのルーターは、一覧表示用のコントローラメソッドを呼び出します。
    -   コントローラは、URLのクエリパラメータ (`search`など) を読み取り、DBから**フィルタリング済みのデータ**を取得します。
    -   コントローラは、取得したデータを`props`としてBladeテンプレートに渡し、完全なHTMLをレンダリングして返します。

2.  **ページ読み込み後のフィルタ操作時**
    -   ページが表示され、Vueコンポーネントが初期化されます。コンポーネントは、URLのクエリパラメータを解釈し、自身のフィルタ状態を初期化します。
    -   ユーザーが検索ボックスに入力するなど、フィルタを操作するとVueがイベントを捕捉します。
    -   Vueコンポーネントは、`axios`等を用いて `/api/admin/users?search=...` のような**データ取得専用のAPIエンドポイント**に非同期でGETリクエストを送信します。
    -   同時に、ブラウザの **History API** (`history.pushState`) を利用して、ページをリロードせずにURLを更新します。
    -   APIから返却されたJSONデータを使って、画面の一覧部分のみを動的に再描画します。

この設計により、`vue-router`のような大規模なSPAライブラリを導入することなく、MPAの安定性とSPAの滑らかな操作性を両立させます。

より詳細な実装手順については、[実装パターンガイド](./IMPLEMENTATION_PATTERNS.md)を参照してください。
---

## 3. マルチテナントと認可設計

オーナーやスタッフが自身の所属する店舗の情報のみを閲覧・操作できるよう、グローバルスコープとPolicyを組み合わせてデータ分離を堅牢に実現する。

-   **グローバルスコープ**: `shop_id` による自動的なクエリ絞り込みを行い、意図しない情報漏洩をシステムレベルで防止する。
-   **Policyによる認可**: 個別のデータに対する操作権限を厳密に検証し、不正アクセスを防ぐ。

#### 関連ドキュメント
-   **認可設計**: [AUTHORIZATION_SPEC.md](./AUTHORIZATION_SPEC.md)

---

## 4. データモデルに関する重要方針

### 4.1. 顧客アカウントモデルと統合ロジック
ゲスト予約と会員登録ユーザーを柔軟に紐付けるため、「マスターアカウント (`users`)」「来店カード (`bookers`)」「予約票 (`bookings`)」の3層モデルを採用する。これにより、予約情報を変更することなく顧客の名寄せ（統合）を可能にする。

### 4.2. データ削除方針
ユーザー退会やメニュー廃止など、マスターデータは **物理削除** を前提とする。削除時はモデルイベントを利用して関連する予約情報 (`bookings`) の外部キーを `NULL` に更新し、参照整合性とデータの匿名性を担保する。

#### 関連ドキュメント
-   **データベース設計**: [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md)
-   **空き時間計算ロジック**: [AVAILABILITY_CALCULATION_LOGIC.md](./AVAILABILITY_CALCULATION_LOGIC.md)
-   **手動予約時のバリデーション**: [MANUAL_BOOKING_VALIDATION_LOGIC.md](./MANUAL_BOOKING_VALIDATION_LOGIC.md)

---

## 5. API 設計方針

各ロール（Booker, Staff, Owner, Admin）に応じたエンドポイントを定義する。

-   **Booker（予約者向け）**: `/shops/{shop_slug}/bookings` (一覧, 作成, 詳細, 変更, キャンセル)
-   **Staff（店舗スタッフ向け）**: `/shops/{shop_slug}/staff/...` (ダッシュボード, 予約管理, シフト管理)
-   **Owner（店舗オーナー向け）**: `/owner/...` (ダッシュボード, 店舗管理, スタッフ管理, 契約管理)
-   **Admin（全体管理者向け）**: `/admin/...` (オーナー管理, 契約管理)

#### 関連ドキュメント

-   **全体**
    -   予約フォーム仕様: [BOOKING_FORM_SPEC.md](./BOOKING_FORM_SPEC.md)
    -   予約管理機能: [RESERVATION_MANAGEMENT_SPEC.md](./RESERVATION_MANAGEMENT_SPEC.md)
    -   店舗管理機能: [SHOP_MANAGEMENT_SPEC.md](./SHOP_MANAGEMENT_SPEC.md)
-   **管理者向け (Admin)**
    -   機能仕様: [FEATURES_ADMIN_SPEC.md](./FEATURES_ADMIN_SPEC.md)
-   **オーナー向け (Owner)**
    -   機能仕様: [FEATURES_OWNER_SPEC.md](./FEATURES_OWNER_SPEC.md)
    -   オンボーディングと契約: [OWNER_ONBOARDING_AND_CONTRACT_SPEC.md](./OWNER_ONBOARDING_AND_CONTRACT_SPEC.md)
-   **スタッフ向け (Staff)**
    -   機能仕様: [FEATURES_STAFF_SPEC.md](./FEATURES_STAFF_SPEC.md)
    -   予約管理機能: [STAFF_RESERVATION_MANAGEMENT_SPEC.md](./STAFF_RESERVATION_MANAGEMENT_SPEC.md)
-   **予約者向け (Booker)**
    -   機能仕様: [FEATURES_BOOKER_SPEC.md](./FEATURES_BOOKER_SPEC.md)
-   **ゲスト向け (Guest)**
    -   機能仕様: [FEATURES_GUEST_SPEC.md](./FEATURES_GUEST_SPEC.md)

---

## 6. ディレクトリ構成

-   `app/Http/Controllers/{Role}/`
-   `app/Services/`
-   `app/Policies/`
-   `resources/views/{role}/`

---

## 7. 開発・テスト関連

#### 関連ドキュメント
-   **テストガイド**: [TESTING_GUIDE.md](./TESTING_GUIDE.md)
-   **ワークフローテスト**: [WORKFLOW_TESTS.md](./WORKFLOW_TESTS.md)
-   **データセットアップ仕様**: [DATA_SETUP_SPEC.md](./DATA_SETUP_SPEC.md)