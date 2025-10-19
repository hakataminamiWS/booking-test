<template>
    <v-container>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>契約申し込み一覧</span>
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
                    :mobile="smAndDown"
                    @update:options="loadItems"
                    hide-default-footer
                    class="elevation-1 mt-4"
                >
                    <template v-slot:item.created_at="{ item }">
                        {{ new Date(item.created_at).toLocaleString() }}
                    </template>

                    <template v-slot:item.contract_status="{ item }">
                        {{ item.contract ? item.contract.status : 'なし' }}
                    </template>

                    <template v-slot:item.actions="{ item }">
                        <v-btn
                            color="secondary"
                            size="small"
                            :href="`/admin/contract-applications/${item.id}`"
                            class="mr-2"
                        >
                            申し込み詳細
                        </v-btn>
                        <v-btn
                            color="primary"
                            size="small"
                            :href="`/admin/contracts/create?application_id=${item.id}`"
                        >
                            契約を作成
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
                                :model-value="filter.column"
                                :items="filterableColumns"
                                item-title="text"
                                item-value="value"
                                label="対象列"
                                dense
                                hide-details
                                @update:modelValue="(newColumn) => onFilterColumnChange(filter, newColumn)"
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
                            <div v-if="getColumnType(filter.column) === 'date-range'">
                                <v-row dense>
                                    <v-col cols="6">
                                        <v-text-field
                                            v-model="filter.value.start"
                                            label="開始日"
                                            type="date"
                                            dense
                                            hide-details
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="6">
                                        <v-text-field
                                            v-model="filter.value.end"
                                            label="終了日"
                                            type="date"
                                            dense
                                            hide-details
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                            </div>
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
                                :items="[{text: '昇順', value: 'asc'}, {text: '降順', value: 'desc'}]"
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

<script setup lang="ts">
import { ref, computed } from "vue";
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
const itemsPerPage = ref(20);
const from = computed(() => (page.value - 1) * itemsPerPage.value + 1);
const to = computed(() => Math.min(page.value * itemsPerPage.value, totalItems.value));
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));
let isInitialLoad = true;

// --- Filtering ---
interface Filter {
    id: number;
    column: string | null;
    value: any; // Allow complex values like date ranges
}

const filterableColumns = ref([
    { text: "申込 ID", value: "id", type: "text" },
    { text: "お客様名称", value: "customer_name", type: "text" },
    { text: "申込日時", value: "created_at", type: "date-range" },
    {
        text: "ステータス",
        value: "statuses",
        type: "select",
        isArray: true,
        items: ["pending", "approved", "rejected"],
    },
    {
        text: "契約状況",
        value: "contract_statuses",
        type: "select",
        isArray: true,
        items: ["active", "expired", "none"],
    },
]);

const filters = ref<Filter[]>([]);
const activeFilters = ref<Filter[]>([]);

const getColumnType = (columnValue: string | null) => {
    if (!columnValue) return "text";
    return filterableColumns.value.find((c) => c.value === columnValue)?.type || "text";
};
const getColumnItems = (columnValue: string | null) => {
    if (!columnValue) return [];
    return filterableColumns.value.find((c) => c.value === columnValue)?.items || [];
};

const onFilterColumnChange = (filter: Filter, newColumn: string | null) => {
    filter.column = newColumn;
    const columnType = getColumnType(newColumn);
    if (columnType === 'date-range') {
        filter.value = { start: null, end: null };
    } else {
        filter.value = null;
    }
};

const addFilter = () => {
    filters.value.push({ id: Date.now(), column: null, value: null });
};

const removeFilter = (id: number) => {
    const index = filters.value.findIndex((f) => f.id === id);
    if (index > -1) {
        filters.value.splice(index, 1);
    }
    // Also remove from active filters if it exists
    const activeIndex = activeFilters.value.findIndex(f => f.id === id);
    if (activeIndex > -1) {
        activeFilters.value.splice(activeIndex, 1);
    }
    applyFilters(false);
};

const activeFiltersText = computed(() => {
    return activeFilters.value.flatMap(f => {
        const column = filterableColumns.value.find(c => c.value === f.column);
        if (!column) return [];

        if (column.type === 'date-range') {
            const result = [];
            if (f.value.start) {
                result.push({ id: f.id + '_start', text: `${column.text} (開始)`, value: f.value.start });
            }
            if (f.value.end) {
                result.push({ id: f.id + '_end', text: `${column.text} (終了)`, value: f.value.end });
            }
            return result;
        } else {
            return [{
                id: f.id,
                text: column.text,
                value: f.value
            }];
        }
    });
});

const applyFilters = (shouldCloseDialog = true) => {
    activeFilters.value = JSON.parse(
        JSON.stringify(filters.value.filter((f) => {
            if (!f.column) return false;
            if (getColumnType(f.column) === 'date-range') {
                return f.value.start || f.value.end;
            }
            return f.value;
        }))
    );
    if (shouldCloseDialog) {
        filterDialog.value = false;
    }
    page.value = 1;
    loadItems({ page: page.value, itemsPerPage: itemsPerPage.value, sortBy: [] });
};

// --- Sorting ---
interface Sort {
    column: string | null;
    order: "asc" | "desc" | null;
}

const sortableColumns = ref([
    { text: "申込 ID", value: "id" },
    { text: "お客様名称", value: "customer_name" },
    { text: "申込日時", value: "created_at" },
]);

const sortBy = ref<Sort>({ column: null, order: null });
const activeSort = ref<Sort>({ column: null, order: null });

const applySort = () => {
    activeSort.value = JSON.parse(JSON.stringify(sortBy.value));
    sortDialog.value = false;
    page.value = 1;
    loadItems({ page: page.value, itemsPerPage: itemsPerPage.value, sortBy: [] });
};

const removeSort = () => {
    sortBy.value = { column: null, order: null };
    activeSort.value = { column: null, order: null };
    page.value = 1;
    loadItems({ page: page.value, itemsPerPage: itemsPerPage.value, sortBy: [] });
};

const sortChipText = computed(() => {
    if (!activeSort.value.column || !activeSort.value.order) return null;
    const column = sortableColumns.value.find(c => c.value === activeSort.value.column);
    if (!column) return null;
    const orderText = activeSort.value.order === 'asc' ? '昇順' : '降順';
    return `並び替え: ${column.text} (${orderText})`;
});


// --- Data Loading ---
const loadItems = async (options: Options) => {
    loading.value = true;

    if (isInitialLoad) {
        const urlParams = new URLSearchParams(window.location.search);
        const urlPage = urlParams.get('page');
        if (urlPage) page.value = parseInt(urlPage, 10);

        const urlSortBy = urlParams.get('sort_by');
        const urlSortOrder = urlParams.get('sort_order') as 'asc' | 'desc' | null;
        if (urlSortBy && urlSortOrder) {
            sortBy.value = { column: urlSortBy, order: urlSortOrder };
            activeSort.value = { column: urlSortBy, order: urlSortOrder };
        }

        const tempFilters: Filter[] = [];
        urlParams.forEach((value, key) => {
            const colName = key.replace(/_after$|_before$|\u005b\u005d$/g, '');
            const columnDef = filterableColumns.value.find(c => c.value === colName);
            if (columnDef) {
                let existingFilter = tempFilters.find(f => f.column === colName);
                if (columnDef.type === 'date-range') {
                    if (!existingFilter) {
                        existingFilter = { id: Date.now() + Math.random(), column: colName, value: { start: null, end: null } };
                        tempFilters.push(existingFilter);
                    }
                    if (key.endsWith('_after')) {
                        existingFilter.value.start = value;
                    } else if (key.endsWith('_before')) {
                        existingFilter.value.end = value;
                    }
                } else {
                     if (!existingFilter) {
                        tempFilters.push({ id: Date.now() + Math.random(), column: colName, value });
                    }
                }
            }
        });

        if (tempFilters.length > 0) {
            filters.value = JSON.parse(JSON.stringify(tempFilters));
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
        if (filter.column && filter.value) {
            const columnDef = filterableColumns.value.find(c => c.value === filter.column);
            if (columnDef?.type === 'date-range') {
                if (filter.value.start) {
                    params.append(`${filter.column}_after`, filter.value.start);
                }
                if (filter.value.end) {
                    params.append(`${filter.column}_before`, filter.value.end);
                }
            } else if (columnDef?.isArray) {
                params.append(`${filter.column}[]`, filter.value);
            } else {
                params.append(filter.column, filter.value);
            }
        }
    });

    const apiUrl = `/admin/api/contract-applications?${params.toString()}`;
    history.pushState(null, '', `/admin/contract-applications?${params.toString()}`);

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
    { title: "申込ID", key: "id", sortable: false },
    { title: "お客様名称", key: "customer_name", sortable: false },
    { title: "申込日時", key: "created_at", sortable: false },
    { title: "ステータス", key: "status", sortable: false },
    { title: "契約状況", key: "contract_status", sortable: false },
    { title: "操作", key: "actions", sortable: false },
];
</script>