# 実装パターンガイド

このドキュメントは、本プロジェクトで繰り返し現れるUIや機能について、一貫性を保ち、効率的に開発を進めるための標準的な実装パターンを定義します。

---

## 動的な一覧画面の実装パターン

検索、ソート、ページネーションを持つすべての一覧画面は、UXと開発効率を両立するため、Vuetifyの`<v-data-table-server>`コンポーネントを利用したサーバーサイド処理を標準パターンとします。

### 1. 目的

- ページ全体のリロードを伴わない快適なフィルタリング、ソート、ページネーション操作を提供する。
- URLに状態（フィルタ条件、ページ番号、ソート順）を反映させ、ブラウザの「戻る」機能やURLの共有を可能にする。
- フロントエンドの実装を簡素化し、ボイラープレートコードを削減する。

### 2. 基本方針

- **コンポーネント**: Vuetifyの`<v-data-table-server>`を全面的に採用する。
- **データ処理**: フィルタリング、ソート、ページネーションのロジックはすべてサーバーサイド（Laravel）に集約する。
- **状態管理**: フロントエンドは、ユーザー操作をトリガーにAPIへリクエストを送信し、返却されたデータを表示することに専念する。URLの更新もフロントエンドが担当する。

### 3. 実装フロー

以下に、`{Role}`（例: Admin）配下の`{Resource}`（例: User）というリソースの一覧画面を実装する場合の一般的な手順を示します。

#### 3.1. バックエンド (Laravel)

##### 3.1.1. APIコントローラの作成

1.  **コントローラ作成**:
    -   `app/Http/Controllers/Api/{Role}/{Resource}Controller.php` のように、API用のコントローラを専用ディレクトリに作成します。

2.  **ロジック実装**:
    -   `index` メソッドで、リクエストからフィルタ、ソート、ページネーションのパラメータを受け取ります。
    -   受け取ったパラメータに基づいてEloquentクエリを構築します（`where`句、`orderBy`句など）。
    -   `paginate()` メソッドでデータを取得し、コントローラはページネーション情報を含んだJSONをそのまま返却します。Laravelの標準Paginatorオブジェクトは、`<v-data-table-server>`が必要とする`data`と`total`を内包しているため、特別な加工は不要です。

    ```php
    // app/Http/Controllers/Api/{Role}/{Resource}Controller.php (実装イメージ)
    public function index(Request $request)
    {
        $query = {Resource}::query(); // 対象のモデル

        // フィルタリング (例: search パラメータ)
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        // ソート
        if ($request->filled('sort_by')) {
            $order = $request->input('sort_order', 'asc');
            $query->orderBy($request->input('sort_by'), $order);
        } else {
            $query->latest(); // デフォルトソート
        }

        // ページネーション
        $paginator = $query->paginate($request->input('per_page', 20));

        return response()->json($paginator);
    }
    ```

##### 3.1.2. ルートの追加

-   `routes/web.php` 内のAPIグループにルート定義を追加します。

    ```php
    // 例: /api/admin/users
    Route::get('/{role}/{resource}', [App\Http\Controllers\Api\{Role}\{Resource}Controller::class, 'index'])->name('api.{role}.{resource}.index');
    ```

#### 3.2. フロントエンド (Vue)

##### 3.2.1. Vueコンポーネントの実装

-   `<v-data-table-server>` を使用してテンプレートを構築します。
-   データ取得ロジックは、`@update:options` イベントをトリガーにして実行します。このイベントは、ページ遷移、ソート順の変更、表示件数の変更時に自動的に発行されます。

##### 3.2.2. テンプレート構成

-   検索フォームなどのフィルタコントロールを配置します。
-   **ページネーション (`<v-pagination>`) をデータテーブルの上に配置します。**
-   件数表示テキスト（例: 全 XX 件中 X-X 件表示）を配置します。
-   `<v-data-table-server>` を配置し、**`hide-default-footer` プロパティを必ず付与します。**

    ```vue
    <!-- resources/js/{role}/{resource}/Index.vue (テンプレートのイメージ) -->
    <template>
      <v-text-field v-model="searchTerm" label="検索"></v-text-field>

      <v-pagination
        v-model="page"
        :length="totalPages"
        :total-visible="5"
      ></v-pagination>

      <div>全 {{ totalItems }} 件</div>

      <v-data-table-server
        v-model:page="page"
        v-model:items-per-page="itemsPerPage"
        v-model:sort-by="sortBy"
        :headers="headers"
        :items="serverItems"
        :items-length="totalItems"
        :loading="loading"
        @update:options="loadItems"
        hide-default-footer
      ></v-data-table-server>
    </template>
    ```

##### 3.2.3. スクリプト構成

コンポーネントのスクリプトは、以下の要素で構成されます。

1.  **状態の定義**: `ref` を使用して、テーブルのアイテム、ローディング状態、ページ情報、フィルタ条件などのリアクティブな状態を定義します。これらの `ref` は、`<script setup>` のトップレベルで、URLのクエリパラメータを元に初期化します。
2.  **`loadItems` メソッド**: `@update:options` イベントによって呼び出され、現在の状態（ページ、ソート順など）とフィルタ条件を元にAPIリクエストを構築・実行します。APIのレスポンスを受けて、状態の `ref` を更新します。**この時、ページネーションコンポーネントと表示を同期させるため、`page` の `ref` も更新します。**
3.  **`manualFetch` メソッド**: 検索ボタンなどから呼び出され、`loadItems` を強制的に実行します。ユーザー体験向上のため、**新しい検索実行時は1ページ目に戻す**のが原則です。

```vue
<!-- resources/js/{role}/{resource}/Index.vue (スクリプトのイメージ) -->
<script setup lang="ts">
import { ref } from 'vue';
import type { VDataTableServer } from 'vuetify/components';
import axios from 'axios';

type Options = InstanceType<typeof VDataTableServer>['$props']['options'];

// --- URLパラメータから初期状態を決定 ---
const urlParams = new URLSearchParams(window.location.search);
const sortByFromUrl = urlParams.get('sort_by');
const initialSortBy = sortByFromUrl
  ? [{ key: sortByFromUrl, order: urlParams.get('sort_order') || 'asc' }]
  : [];

// --- コンポーネントのリアクティブな状態を定義 ---
const serverItems = ref<any[]>([]);
const loading = ref(false);
const totalItems = ref(0);
const totalPages = ref(0);
const page = ref(Number(urlParams.get('page')) || 1);
const itemsPerPage = ref(Number(urlParams.get('per_page')) || 20);
const searchTerm = ref(urlParams.get('search') || '');
const sortBy = ref<any[]>(initialSortBy);
const options = ref<Options>({} as Options);

const headers = [ /* ... */ ];

const loadItems = async (newOptions: Options) => {
  options.value = newOptions;
  page.value = newOptions.page; // ページネーションの見た目をデータと同期
  loading.value = true;

  const params = new URLSearchParams();
  params.append('page', String(newOptions.page));
  params.append('per_page', String(newOptions.itemsPerPage));

  if (newOptions.sortBy.length) {
    params.append('sort_by', newOptions.sortBy[0].key);
    params.append('sort_order', newOptions.sortBy[0].order);
  }
  if (searchTerm.value) {
    params.append('search', searchTerm.value);
  }

  const apiUrl = `/api/{role}/{resource}?${params.toString()}`;
  const response = await axios.get(apiUrl);

  serverItems.value = response.data.data;
  totalItems.value = response.data.total;
  totalPages.value = response.data.last_page;
  loading.value = false;

  const newUrl = `/{role}/{resource}?${params.toString()}`;
  history.pushState(null, '', newUrl);
};

const manualFetch = () => {
  // 現在のソート順などを維持しつつ、1ページ目を強制的に読み込む
  loadItems({ ...options.value, page: 1 });
};
</script>
```
