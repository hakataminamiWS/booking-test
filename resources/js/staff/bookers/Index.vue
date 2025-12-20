<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn
                       :href="shopShowUrl"
                       prepend-icon="mdi-arrow-left"
                       variant="text">
                    店舗詳細へ戻る
                </v-btn>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="shop" />
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title
                                  :class="{
                                    'd-flex': true,
                                    'flex-column': smAndDown,
                                    'align-start': smAndDown,
                                    'justify-space-between': !smAndDown,
                                    'align-center': !smAndDown,
                                }">
                        <span>予約者一覧</span>
                        <v-btn
                               prepend-icon="mdi-plus"
                               :href="`/shops/${props.shop.slug}/staff/bookers/create`"
                               :class="{ 'mt-2': smAndDown }"
                               color="primary">
                            予約者を新規登録する
                        </v-btn>
                    </v-card-title>
                    <v-card-text>
                        <!-- ControlBar: Filter, Sort, Total Items Count, Pagination, etc. -->
                        <v-row class="align-center mb-2" dense>
                            <!-- Filter Button -->
                            <v-col cols="auto">
                                <v-btn
                                       variant="tonal"
                                       @click="filterDialog = true"
                                       append-icon="mdi-filter-variant">
                                    絞り込み
                                </v-btn>
                            </v-col>

                            <!-- Sort Button -->
                            <v-col cols="auto">
                                <v-btn
                                       variant="tonal"
                                       @click="sortDialog = true"
                                       prepend-icon="mdi-sort">
                                    並び替え
                                </v-btn>
                            </v-col>

                            <v-spacer></v-spacer>

                            <!-- Total Items Count -->
                            <v-col cols="auto">
                                <span class="text-body-2">全 {{ totalItems }} 件中 {{ from }} -
                                    {{ to }} 件表示</span>
                            </v-col>

                            <!-- Pagination -->
                            <v-col cols="auto">
                                <v-pagination
                                              v-model="page"
                                              :length="totalPages"
                                              :total-visible="5"
                                              density="compact"></v-pagination>
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
                                        @click:close="removeFilter(filter.id)">
                                    {{ filter.text }}: {{ filter.value }}
                                </v-chip>
                                <v-chip
                                        v-if="sortChipText"
                                        class="mr-2 mb-2"
                                        closable
                                        @click:close="removeSort">
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
                                             :mobile="smAndDown"
                                             @update:options="loadItems"
                                             hide-default-footer
                                             class="elevation-1 mt-4">
                            <template v-slot:item.updated_at="{ item }">
                                {{ new Date(item.updated_at).toLocaleString() }}
                            </template>
                            <template v-slot:item.last_booking_at="{ item }">
                                <span v-if="item.crm && item.crm.last_booking_at">
                                    {{ item.crm.last_booking_at.substring(0, 10) }}
                                </span>
                            </template>
                            <template v-slot:item.actions="{ item }">
                                <v-btn
                                       color="primary"
                                       :href="`/shops/${props.shop.slug}/staff/bookers/${item.id}/edit`">編集する
                                </v-btn>
                            </template>
                        </v-data-table-server>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Filter Dialog -->
        <v-dialog v-model="filterDialog" max-width="800px">
            <v-card>
                <v-card-title>絞り込み</v-card-title>
                <v-card-text>
                    <v-row v-for="filter in filters" :key="filter.id" align="center">
                        <v-col cols="4">
                            <v-select v-model="filter.column" :items="filterableColumns" item-title="text"
                                      item-value="value"
                                      label="対象列" dense hide-details></v-select>
                        </v-col>
                        <v-col cols="7">
                            <v-text-field v-if="getColumnType(filter.column) === 'text'" v-model="filter.value"
                                          label="値" dense
                                          hide-details></v-text-field>
                            <div v-if="
                                getColumnType(filter.column) ===
                                'date-range'
                            " class="d-flex align-center">
                                <v-text-field v-model="filter.value" label="開始日 (YYYY-MM-DD)" dense hide-details
                                              class="mr-2"></v-text-field>
                                <span>-</span>
                                <v-text-field v-model="filter.value_to" label="終了日 (YYYY-MM-DD)" dense hide-details
                                              class="ml-2"></v-text-field>
                            </div>
                            <div v-if="
                                getColumnType(filter.column) ===
                                'number-range'
                            " class="d-flex align-center">
                                <v-text-field v-model="filter.value" label="以上" dense hide-details class="mr-2"
                                              type="number"></v-text-field>
                                <span>-</span>
                                <v-text-field v-model="filter.value_to" label="以下" dense hide-details class="ml-2"
                                              type="number"></v-text-field>
                            </div>
                        </v-col>
                        <v-col cols="1">
                            <v-btn icon size="small" @click="removeFilter(filter.id)">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>
                    <v-btn text @click="addFilter" class="mt-4">+ フィルタを追加</v-btn>
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
                            <v-select v-model="sortBy.column" :items="sortableColumns" item-title="text"
                                      item-value="value"
                                      label="対象列" dense hide-details></v-select>
                        </v-col>
                        <v-col cols="6">
                            <v-select v-model="sortBy.order" :items="[
                                { text: '昇順', value: 'asc' },
                                { text: '降順', value: 'desc' },
                            ]" item-title="text" item-value="value" label="順序" dense hide-details></v-select>
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

<script setup lang="ts">
import { ref, computed } from "vue";
import type { VDataTableServer } from "vuetify/components";
import axios from "axios";
import { useDisplay } from "vuetify";
import ShopHeader from "@/components/common/ShopHeader.vue";

const props = defineProps<{
    shop: { name: string; slug: string };
}>();

const shopShowUrl = computed(() => `/shops/${props.shop.slug}/staff/bookings`);

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
const itemsPerPage = ref(20);
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
    value: any;
    value_to?: any;
}

const filterableColumns = ref([
    { text: "会員番号", value: "number", type: "text" },
    { text: "名前", value: "name", type: "text" },
    { text: "よみかた", value: "name_kana", type: "text" },
    { text: "連絡先メールアドレス", value: "contact_email", type: "text" },
    { text: "連絡先電話番号", value: "contact_phone", type: "text" },
    { text: "最終予約日時", value: "last_booking_at", type: "date-range" },
    { text: "予約回数", value: "booking_count", type: "number-range" },
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

const addFilter = () => {
    filters.value.push({ id: Date.now(), column: null, value: null });
};

const removeFilter = (id: number) => {
    const index = activeFilters.value.findIndex((f) => f.id === id);
    if (index > -1) {
        activeFilters.value.splice(index, 1);
    }
    const fIndex = filters.value.findIndex((f) => f.id === id);
    if (fIndex > -1) {
        filters.value.splice(fIndex, 1);
    }
    page.value = 1;
    loadItems({
        page: page.value,
        itemsPerPage: itemsPerPage.value,
        sortBy: [],
    });
};

const activeFiltersText = computed(() => {
    return activeFilters.value.map((f) => {
        const column = filterableColumns.value.find(
            (c) => c.value === f.column
        );
        let displayValue: string | null = f.value;

        if (
            column?.type === "date-range" ||
            column?.type === "number-range"
        ) {
            if (f.value && f.value_to) {
                displayValue = `${f.value} - ${f.value_to}`;
            } else if (f.value) {
                displayValue = `${f.value} 以降`;
            } else if (f.value_to) {
                displayValue = `${f.value_to} 以前`;
            }
        }

        return {
            id: f.id,
            text: column ? column.text : "",
            value: displayValue,
        };
    });
});

const applyFilters = (shouldCloseDialog = true) => {
    activeFilters.value = JSON.parse(
        JSON.stringify(
            filters.value.filter((f) => f.column && (f.value || f.value_to))
        )
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
    { text: "会員番号", value: "number" },
    { text: "名前", value: "name" },
    { text: "よみかた", value: "name_kana" },
    { text: "連絡先メールアドレス", value: "contact_email" },
    { text: "最終予約日時", value: "last_booking_at" },
    { text: "予約回数", value: "booking_count" },
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

    if (isInitialLoad) {
        const urlParams = new URLSearchParams(window.location.search);
        const urlPage = urlParams.get("page");
        if (urlPage) page.value = parseInt(urlPage, 10);

        const urlSortBy = urlParams.get("sort_by");
        const urlSortOrder = urlParams.get("sort_order") as
            | "asc"
            | "desc"
            | null;
        if (urlSortBy && urlSortOrder) {
            sortBy.value = { column: urlSortBy, order: urlSortOrder };
            activeSort.value = { column: urlSortBy, order: urlSortOrder };
        }

        const tempFilters: Filter[] = [];
        filterableColumns.value.forEach((col) => {
            if (col.type.endsWith("-range")) {
                const from = urlParams.get(`${col.value}_from`);
                const to = urlParams.get(`${col.value}_to`);
                if (from || to) {
                    tempFilters.push({
                        id: Date.now() + Math.random(),
                        column: col.value,
                        value: from,
                        value_to: to,
                    });
                }
            } else {
                if (urlParams.has(col.value)) {
                    tempFilters.push({
                        id: Date.now() + Math.random(),
                        column: col.value,
                        value: urlParams.get(col.value),
                    });
                }
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

    if (activeSort.value.column && activeSort.value.order) {
        params.append("sort_by", activeSort.value.column);
        params.append("sort_order", activeSort.value.order);
    }

    activeFilters.value.forEach((filter) => {
        const columnDef = filterableColumns.value.find(
            (c) => c.value === filter.column
        );
        if (columnDef?.type.endsWith("-range")) {
            if (filter.value) {
                params.append(`${filter.column}_from`, filter.value);
            }
            if (filter.value_to) {
                params.append(`${filter.column}_to`, filter.value_to);
            }
        } else {
            if (filter.column && filter.value) {
                params.append(filter.column, filter.value);
            }
        }
    });

    const apiUrl = `/shops/${props.shop.slug}/staff/api/bookers?${params.toString()}`;
    history.pushState(
        null,
        "",
        `/shops/${props.shop.slug}/staff/bookers?${params.toString()}`
    );

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
    { title: "会員番号", key: "number", sortable: false },
    { title: "名前", key: "name", sortable: false },
    { title: "よみかた", key: "crm.name_kana", sortable: false },
    { title: "連絡先メールアドレス", key: "contact_email", sortable: false },
    { title: "連絡先電話番号", key: "contact_phone", sortable: false },
    { title: "最終予約日時", key: "last_booking_at", sortable: false },
    { title: "予約回数", key: "crm.booking_count", sortable: false },
    { title: "操作", key: "actions", sortable: false },
];
</script>
