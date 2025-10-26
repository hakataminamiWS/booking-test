# 実装パターンガイド-一覧画面

このドキュメントは、本プロジェクトで繰り返し現れる一覧画面の UI や機能について、一貫性を保ち、効率的に開発を進めるための標準的な実装パターンを定義します。

---

## 動的な一覧画面の実装パターン

検索、ソート、ページネーションを持つすべての一覧画面は、UX と開発効率、拡張性を両立するため、Vuetify の`<v-data-table-server>`コンポーネントと動的フィルタダイアログを組み合わせたサーバーサイド処理を標準パターンとします。

### 1. 目的

-   ページ全体のリロードを伴わない快適なフィルタリング、ソート、ページネーション操作を提供する。
-   フィルタ項目が増えても UI の変更が最小限で済む、スケーラブルなフィルタ機能を提供する。
-   URL に状態（フィルタ条件、ページ番号、ソート順）を反映させ、ブラウザの「戻る」機能や URL の共有を可能にする。

### 2. 基本方針

-   **コンポーネント**: Vuetify の`<v-data-table-server>`を全面的に採用する。
-   **データ処理**: フィルタリング、ソート、ページネーションのロジックはすべてサーバーサイド（Laravel）に集約する。
-   **初期画面表示**: Blade テンプレートからは CSRF トークン等の最低限の情報のみを受け取り、一覧データは Vue コンポーネントのマウント後に API を呼び出して取得する。
-   **状態管理**: フロントエンドは、ユーザー操作をトリガーに API へリクエストを送信し、返却されたデータを表示することに専念する。URL の更新もフロントエンドが担当する。
-   **モバイル対応**: Vuetify 3 の `mobile` プロパティを利用し、PC 画面幅が < 960px の場合の場合に自動的にモバイルレイアウトへ切り替える
-   **レスポンシブ設計**: **モバイル優先**で UI を設計し、PC 表示時に「一覧性・効率性」を補完

### 3. フロントエンド (Vue)

#### 3.1. 表示項目一覧（例）

一覧画面に表示される項目とその仕様を定義します。画面仕様詳細の表示項目一覧に記載される予定であり、実装の際は対象ドキュメントを確認して実装される。

| カラム名 | データソース                                                                        | フィルタ                | ソート | 操作 |
| :------- | :---------------------------------------------------------------------------------- | :---------------------- | :----- | :--- |
| 役割     | {Owner かどうか。}                                                                  | 可 (セレクト)           | 可     |
| 登録日時 | users.created_at {クライアントのタイムゾーンに合わせる 例：yyyy-mm-ddThh:mm:ss JST} | 可 (日付選択、範囲指定) | 可     |
| 操作     | -                                                                                   | 不可                    | 不可   |

#### 3.2. UI 構成

-   **全体**
    全体のレイアウトは`<v-card>`で囲む。

-   **タイトル**  
    ウィンドウのタイトルと、画面上部に表示するタイトルは同じにする。`v-card-title`を利用する。

-   **コントロールバー**
    絞り込みボタン、並び替えボタン、件数表示、ページネーション
-   **絞り込みボタン**  
    アイコン付きボタンで「絞り込み」を表示する。
    テキストの後ろにアイコンを配置 (`append-icon`)。
    クリックでフィルタダイアログを開く。
-   **並び替えボタン**  
    アイコン付きボタンで「並び替え」を表示する。
    テキストの前にアイコンを配置 (`prepend-icon`)。
    クリックでソートダイアログを開く。
-   **件数表示**  
     `v-spacer`で右寄せにし、`全 X 件中 Y - Z 件表示`の形式で表示。
-   **ページネーション**  
    `v-pagination` を利用する。表示するボタンは最初、最後、前、後、現在の 5

-   **適用中フィルタ、ソート表示**
    **コントロールバー**の下に、適用されているフィルタ、およびソートを`<v-chip>`で一覧表示する。クローズ可能とする。フィルタ、ソートが指定された場合に、この要素の枠を確保し、表示する。

-   **フィルタダイアログ**: `<v-dialog>`内に、動的にフィルタ条件を追加・削除できる UI を構築します。
    各フィルタ行は「対象列の選択 (`v-select`)」「値の入力（`v-text-field` or `v-select`）」「削除ボタン」で構成します。「+ フィルタを追加」ボタンで新しいフィルタ行を追加します。

-   **ソートダイアログ**: `<v-dialog>`内に、動的にソート条件を追加・削除できる UI を構築します。
    各フィルタ行は「対象列の選択 (`v-select`)」「昇順 or 降順の選択（`v-select`）」「削除ボタン」で構成します。原則、ソート列は一つだけとします。

-   **データテーブル**: `<v-data-table-server>`を配置し、`hide-default-footer`プロパティを付与します。

-   **ソート**  
    サーバーサイドソート。UI は、`v-data-table-server`の機能は利用しない。PC レイアウトにおいて、ヘッダーをクリックしても URL の変更や、ソートは行わない。

-   **操作列**  
    末尾列「操作」列を追加する。操作用ボタンを配置する。ボタンの名称はそれぞれ操作内容によって異なるので、画面仕様詳細の表示項目一覧に記載される予定であり、実装の際は対象ドキュメントを確認して実装される。

```vue
<!-- resources/js/{role}/{resource}/Index.vue (テンプレートのイメージ) -->
<template>
    <v-container>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>{画面のタイトル}</span>
            </v-card-title>
            <v-card-text>
                <!-- ControlBar: Filter, Sort, Total Items Count, Pagination, etc. -->
                <v-row class="align-center mb-2" dense>
                    <!-- Filter Button -->
                    <v-col cols="auto">
                        <v-btn
                            variant="tonal"
                            @click="filterDialog = true"
                            append-icon="mdi-filter-variant"
                        >
                            絞り込み
                        </v-btn>
                    </v-col>

                    <!-- Sort Button -->
                    <v-col cols="auto">
                        <v-btn
                            variant="tonal"
                            @click="sortDialog = true"
                            prepend-icon="mdi-sort"
                        >
                            並び替え
                        </v-btn>
                    </v-col>

                    <v-spacer></v-spacer>

                    <!-- Total Items Count -->
                    <v-col cols="auto">
                        <span class="text-body-2"
                            >全 {{ totalItems }} 件中 {{ from }} -
                            {{ to }} 件表示</span
                        >
                    </v-col>

                    <!-- Pagination -->
                    <v-col cols="auto">
                        <v-pagination
                            v-model="page"
                            :length="totalPages"
                            :total-visible="5"
                            density="compact"
                        ></v-pagination>
                    </v-col>
                </v-row>

                <!-- Applied Filters & Sort Chips -->
                <v-row dense>
                    <v-col cols="12">
                        <v-chip
                            v-for="filter in activeFiltersText"
                            :key="filter.id"
                            class="mr-2 mb-2"
                            closable
                            @click:close="removeFilter(filter.id)"
                        >
                            {{ filter.text }}: {{ filter.value }}
                        </v-chip>
                        <v-chip
                            v-if="sortChipText"
                            class="mr-2 mb-2"
                            closable
                            @click:close="removeSort"
                        >
                            {{ sortChipText }}
                        </v-chip>
                    </v-col>
                </v-row>

                <!-- Data Table -->
                <v-data-table-server
                    v-model:page="page"
                    v-model:items-per-page="itemsPerPage"
                    :headers="headers"
                    :items="serverItems"
                    :items-length="totalItems"
                    :loading="loading"
                    @update:options="loadItems"
                    hide-default-footer
                    class="elevation-1 mt-4"
                >
                    <!-- slotの定義 -->
                    <template v-slot:item.created_at="{ item }">
                        {{ new Date(item.created_at).toLocaleString() }}
                    </template>

                    <!-- slotの定義 -->
                    <template v-slot:item.actions="{ item }">
                        <v-btn
                            color="primary"
                            size="small"
                            :href="`{リンク先URL}`"
                        >
                            {操作のボタンラベル}
                        </v-btn>
                    </template>
                </v-data-table-server>
            </v-card-text>
        </v-card>

        <!-- Filter Dialog -->
        <v-dialog v-model="filterDialog" max-width="800px">
            <v-card>
                <v-card-title>絞り込み</v-card-title>
                <v-card-text>
                    <v-row
                        v-for="filter in filters"
                        :key="filter.id"
                        align="center"
                    >
                        <v-col cols="4">
                            <v-select
                                v-model="filter.column"
                                :items="filterableColumns"
                                item-title="text"
                                item-value="value"
                                label="対象列"
                                dense
                                hide-details
                            ></v-select>
                        </v-col>
                        <v-col cols="7">
                            <v-text-field
                                v-if="getColumnType(filter.column) === 'text'"
                                v-model="filter.value"
                                label="値"
                                dense
                                hide-details
                            ></v-text-field>
                            <v-select
                                v-if="getColumnType(filter.column) === 'select'"
                                v-model="filter.value"
                                :items="getColumnItems(filter.column)"
                                label="値"
                                dense
                                hide-details
                            ></v-select>
                        </v-col>
                        <v-col cols="1">
                            <v-btn
                                icon
                                size="small"
                                @click="removeFilter(filter.id)"
                            >
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>
                    <v-btn text @click="addFilter" class="mt-4"
                        >+ フィルタを追加</v-btn
                    >
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="filterDialog = false">キャンセル</v-btn>
                    <v-btn color="primary" @click="applyFilters">適用</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Sort Dialog -->
        <v-dialog v-model="sortDialog" max-width="500px">
            <v-card>
                <v-card-title>並び替え</v-card-title>
                <v-card-text>
                    <v-row align="center">
                        <v-col cols="6">
                            <v-select
                                v-model="sortBy.column"
                                :items="sortableColumns"
                                item-title="text"
                                item-value="value"
                                label="対象列"
                                dense
                                hide-details
                            ></v-select>
                        </v-col>
                        <v-col cols="6">
                            <v-select
                                v-model="sortBy.order"
                                :items="[
                                    { text: '昇順', value: 'asc' },
                                    { text: '降順', value: 'desc' },
                                ]"
                                item-title="text"
                                item-value="value"
                                label="順序"
                                dense
                                hide-details
                            ></v-select>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="sortDialog = false">キャンセル</v-btn>
                    <v-btn color="primary" @click="applySort">適用</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>
```

#### 3.3. スクリプト構成 (`<script setup lang="ts">`)

コンポーネントのスクリプトは、以下の要素で構成されます。

1.  **状態の定義**: `ref` を使用して、テーブルのアイテム、ローディング状態、ページ情報、ダイアログの表示状態（フィルタ用・ソート用）、初回読み込み完了フラグなどを定義します。
2.  **フィルタ・ソート関連の定義**:
    -   `Filter` / `Sort` インターフェースを定義します。
    -   `filterableColumns` / `sortableColumns`: フィルタやソートが可能な列の定義情報（表示名、内部名など）を保持します。
    -   `filters` / `sortBy`: ダイアログ内の現在の設定や、実際に適用されている条件を保持する`ref`を定義します。
3.  **ヘルパー関数**: `getColumnType`など、動的 UI を実現するための補助関数を定義します。
4.  **フィルタ・ソート操作関数**: `addFilter`, `removeFilter`, `applyFilters`, `applySort`, `removeSort`などを定義します。
5.  **表示用`computed`プロパティ**: `activeFiltersText` や `sortChipText` など、適用中の条件をチップに表示するためのテキストを生成します。
6.  **データ取得と URL 同期 (`loadItems`)**: `@update:options`イベントから呼び出される中心的な関数です。以下の責務を持ちます。
    -   **初回読み込み時の状態復元**: 初めて実行される際に、現在の URL のクエリパラメータを解釈し、ページ、ソート順、フィルタの状態をコンポーネントの`ref`に反映します。
    -   **API リクエストの構築**: 現在のページ、ソート順、フィルタ条件を元に、API リクエストのクエリパラメータを動的に構築します。
    -   **URL の更新**: 構築したクエリパラメータを元にブラウザの URL を`history.pushState`で更新し、リロードしても状態が復元できるようにします。
    -   **API リクエストの実行**: `axios`で API を呼び出し、取得したデータをテーブルに反映します。

```vue
<!-- resources/js/{role}/{resource}/Index.vue (スクリプトのイメージ) -->
<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import type { VDataTableServer } from "vuetify/components";
import axios from "axios";
import { useDisplay } from "vuetify";

const { smAndDown } = useDisplay();

type Options = InstanceType<typeof VDataTableServer>["$props"]["options"];
type Headers = InstanceType<typeof VDataTableServer>["$props"]["headers"];

// --- Component State ---
const filterDialog = ref(false);
const sortDialog = ref(false);
const serverItems = ref<any[]>([]);
const loading = ref(false);
const totalItems = ref(0);
const page = ref(1);
const itemsPerPage = ref(20); // Or your default
const from = computed(() => (page.value - 1) * itemsPerPage.value + 1);
const to = computed(() =>
    Math.min(page.value * itemsPerPage.value, totalItems.value)
);
const totalPages = computed(() =>
    Math.ceil(totalItems.value / itemsPerPage.value)
);
let isInitialLoad = true;

// --- Filtering ---
interface Filter {
    id: number;
    column: string | null;
    value: string | null;
}

const filterableColumns = ref([
    { text: "お客様名称", value: "customer_name", type: "text" },
    {
        text: "ステータス",
        value: "status",
        type: "select",
        items: ["pending", "approved", "rejected"],
    },
]);

const filters = ref<Filter[]>([]);
const activeFilters = ref<Filter[]>([]);

const getColumnType = (columnValue: string | null) => {
    if (!columnValue) return "text";
    return (
        filterableColumns.value.find((c) => c.value === columnValue)?.type ||
        "text"
    );
};
const getColumnItems = (columnValue: string | null) => {
    if (!columnValue) return [];
    return (
        filterableColumns.value.find((c) => c.value === columnValue)?.items ||
        []
    );
};

const addFilter = () => {
    filters.value.push({ id: Date.now(), column: null, value: null });
};

const removeFilter = (id: number) => {
    const index = filters.value.findIndex((f) => f.id === id);
    if (index > -1) {
        filters.value.splice(index, 1);
    }
    applyFilters(false);
};

const activeFiltersText = computed(() => {
    return activeFilters.value.map((f) => {
        const column = filterableColumns.value.find(
            (c) => c.value === f.column
        );
        return {
            id: f.id,
            text: column ? column.text : "",
            value: f.value,
        };
    });
});

const applyFilters = (shouldCloseDialog = true) => {
    activeFilters.value = JSON.parse(
        JSON.stringify(filters.value.filter((f) => f.column && f.value))
    );
    if (shouldCloseDialog) {
        filterDialog.value = false;
    }
    page.value = 1;
    loadItems({
        page: page.value,
        itemsPerPage: itemsPerPage.value,
        sortBy: [],
    });
};

// --- Sorting ---
interface Sort {
    column: string | null;
    order: "asc" | "desc" | null;
}

const sortableColumns = ref([
    { text: "登録日時", value: "created_at" },
    { text: "お客様名称", value: "customer_name" },
]);

const sortBy = ref<Sort>({ column: null, order: null });
const activeSort = ref<Sort>({ column: null, order: null });

const applySort = () => {
    activeSort.value = JSON.parse(JSON.stringify(sortBy.value));
    sortDialog.value = false;
    page.value = 1;
    loadItems({
        page: page.value,
        itemsPerPage: itemsPerPage.value,
        sortBy: [],
    });
};

const removeSort = () => {
    sortBy.value = { column: null, order: null };
    activeSort.value = { column: null, order: null };
    page.value = 1;
    loadItems({
        page: page.value,
        itemsPerPage: itemsPerPage.value,
        sortBy: [],
    });
};

const sortChipText = computed(() => {
    if (!activeSort.value.column || !activeSort.value.order) return null;
    const column = sortableColumns.value.find(
        (c) => c.value === activeSort.value.column
    );
    if (!column) return null;
    const orderText = activeSort.value.order === "asc" ? "昇順" : "降順";
    return `並び替え: ${column.text} (${orderText})`;
});

// --- Data Loading ---
const loadItems = async (options: Options) => {
    loading.value = true;

    // 初回読み込み時にURLから状態を復元
    if (isInitialLoad) {
        const urlParams = new URLSearchParams(window.location.search);
        const urlPage = urlParams.get("page");
        if (urlPage) page.value = parseInt(urlPage, 10);

        // ソートの復元
        const urlSortBy = urlParams.get("sort_by");
        const urlSortOrder = urlParams.get("sort_order") as
            | "asc"
            | "desc"
            | null;
        if (urlSortBy && urlSortOrder) {
            sortBy.value = { column: urlSortBy, order: urlSortOrder };
            activeSort.value = { column: urlSortBy, order: urlSortOrder };
        }

        // フィルタの復元
        const tempFilters: Filter[] = [];
        urlParams.forEach((value, key) => {
            const columnDef = filterableColumns.value.find(
                (c) => c.value === key || `statuses[]` === key
            );
            if (columnDef) {
                // `statuses[]`のような配列パラメータを考慮
                const colVal = key.endsWith("[]") ? key.slice(0, -2) : key;
                tempFilters.push({
                    id: Date.now() + Math.random(),
                    column: colVal,
                    value,
                });
            }
        });
        if (tempFilters.length > 0) {
            filters.value = tempFilters;
            activeFilters.value = JSON.parse(JSON.stringify(tempFilters));
        }

        isInitialLoad = false;
    } else {
        page.value = options.page;
    }

    const params = new URLSearchParams();
    params.append("page", page.value.toString());
    params.append("per_page", itemsPerPage.value.toString());

    // ソート情報をparamsに追加
    if (activeSort.value.column && activeSort.value.order) {
        params.append("sort_by", activeSort.value.column);
        params.append("sort_order", activeSort.value.order);
    }

    // 動的にフィルタ条件をparamsに追加
    activeFilters.value.forEach((filter) => {
        if (filter.column && filter.value) {
            if (filter.column === "status") {
                params.append("statuses[]", filter.value);
            } else {
                params.append(filter.column, filter.value);
            }
        }
    });

    const apiUrl = `/api/{role}/{resource}?${params.toString()}`;
    history.pushState(null, "", `/{role}/{resource}?${params.toString()}`);

    try {
        const response = await axios.get(apiUrl);
        serverItems.value = response.data.data;
        totalItems.value = response.data.total;
    } catch (error) {
        console.error("Failed to load items:", error);
    } finally {
        loading.value = false;
    }
};

const headers: Headers = [
    { title: "役割", key: "role", sortable: false },
    { title: "登録日時", key: "created_at", sortable: false },
    { title: "操作", key: "actions", sortable: false },
];
</script>
```

### 4. バックエンド (Laravel)

##### 4.1. API コントローラの責務

`index` メソッドは、HTTP リクエストを受け取り、動的なクエリを構築して、JSON 形式で結果を返却する責務を負います。具体的な実装は以下の指針に従います。

-   **クエリ構築の基本方針**:

    -   Eloquent のクエリビルダをベースに処理を組み立てます。
    -   必要に応じて、`with()` を使用して Eager Loading を行い、N+1 問題を回避します。

-   **動的フィルタリングの実装**:

    -   コントローラ内に、フィルタリングを許可するカラムとその検索方法（完全一致、部分一致、IN 句など）を定義した設定配列（例: `$filterable`）を用意します。
    -   リクエストに含まれるクエリパラメータをループ処理し、上記の設定配列に存在するキーのみをフィルタ条件として適用することで、意図しないカラムでの検索を防ぎます。
    -   リレーション先のテーブルを検索する必要がある場合は `whereHas()` を利用します。
    -   複数の値を許容するフィルタ（例: `statuses[]=pending&statuses[]=approved`）には `whereIn()` を利用します。

-   **ソート機能の実装**:

    -   `sort_by`（ソート対象カラム）と `sort_order`（昇順/降順）クエリパラメータを受け取ります。
    -   指定がある場合は `orderBy()` を適用し、指定がない場合は `latest()` などのデフォルトのソート順を適用します。

-   **ページネーション機能の実装**:

    -   `per_page` クエリパラメータで 1 ページあたりの表示件数を指定できるようにします。
    -   最終的に構築したクエリに対して `paginate()` メソッドを呼び出し、ページネーションされた結果を取得します。

-   **レスポンス**:
    -   ページネーション結果のオブジェクトをそのまま JSON として返却します。これにより、フロントエンドは合計件数や現在のページ番号などの情報を取得できます。

##### 4.1.3. リレーション先カラムのソート

`whereHas`など、リレーション先のテーブルを条件に含む一覧表示において、リレーション先のカラム（`null`になる可能性があるカラム）でソートを行う場合の挙動は、データベース（MySQL）のデフォルト仕様に準拠します。

-   **昇順 (ASC) の場合**: `NULL`のレコードが**先頭**に表示されます。
-   **降順 (DESC) の場合**: `NULL`のレコードが**末尾**に表示されます。

この挙動を前提としてフロントエンドの実装を行ってください。
