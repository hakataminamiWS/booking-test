<template>
    <v-container>
        <ShopHeader :shop="shop" />
        
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>予約一覧</span>
                <v-btn
                    color="primary"
                    :href="`/owner/shops/${shop.slug}/bookings/create`"
                    prepend-icon="mdi-plus"
                >
                    手動で予約を登録する
                </v-btn>
            </v-card-title>
            <v-card-text>
                <!-- ControlBar -->
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
                    <!-- v-data-table-server handles pagination internally via footer by default, 
                         but pattern says hide-default-footer and use custom pagination? 
                         Pattern says: "v-pagination を利用する" and "hide-default-footer" on table.
                    -->
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
                    <!-- Start At -->
                    <template v-slot:item.start_at="{ item }">
                        {{ formatDateTime(item.start_at) }}
                    </template>

                    <!-- Status -->
                    <template v-slot:item.status="{ item }">
                        <v-chip :color="getStatusColor(item.status)" size="small">
                            {{ getStatusText(item.status) }}
                        </v-chip>
                    </template>

                    <!-- Price -->
                    <template v-slot:item.total_price="{ item }">
                        {{ formatCurrency(item.total_price) }}
                    </template>
                    
                    <!-- Channel -->
                    <template v-slot:item.booking_channel="{ item }">
                         {{ getChannelText(item.booking_channel) }}
                    </template>

                    <!-- Actions -->
                    <template v-slot:item.actions="{ item }">
                        <v-btn
                            color="primary"
                            size="small"
                            variant="text"
                            :href="`/owner/shops/${shop.slug}/bookings/${item.id}/edit`"
                        >
                            編集
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
                            <v-text-field
                                v-if="getColumnType(filter.column) === 'number'"
                                v-model="filter.value"
                                type="number"
                                label="値"
                                dense
                                hide-details
                            ></v-text-field>
                            <v-text-field
                                v-if="getColumnType(filter.column) === 'date'"
                                v-model="filter.value"
                                type="date"
                                label="日付"
                                dense
                                hide-details
                            ></v-text-field>
                            <v-select
                                v-if="getColumnType(filter.column) === 'select'"
                                v-model="filter.value"
                                :items="getColumnItems(filter.column)"
                                item-title="text"
                                item-value="value"
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
                                variant="text"
                            >
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>
                    <v-btn variant="text" @click="addFilter" class="mt-4" prepend-icon="mdi-plus"
                        >フィルタを追加</v-btn
                    >
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn variant="text" @click="filterDialog = false">キャンセル</v-btn>
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
                    <v-btn variant="text" @click="sortDialog = false">キャンセル</v-btn>
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
import ShopHeader from "@/owner/shops/components/ShopHeader.vue";

interface Shop {
    id: number;
    name: string;
    slug: string;
    // other fields if needed
}

interface Menu {
    id: number;
    name: string;
}

interface Staff {
    id: number;
    name: string;
}

const props = defineProps<{
    shop: Shop;
    menus: Menu[];
    staffs: Staff[];
}>();

const { smAndDown } = useDisplay();

// type Options = InstanceType<typeof VDataTableServer>["$props"]["options"];
// type Headers = InstanceType<typeof VDataTableServer>["$props"]["headers"];

interface Options {
    page: number;
    itemsPerPage: number;
    sortBy: readonly any[];
    groupBy: readonly any[];
    search: string;
}

// Manually define Headers type or use any for now to avoid complexity if strict type is elusive
type Headers = any;

// --- Component State ---
const filterDialog = ref(false);
const sortDialog = ref(false);
const serverItems = ref<any[]>([]);
const loading = ref(false);
const totalItems = ref(0);
const page = ref(1);
const itemsPerPage = ref(20);
const from = computed(() => {
    if (totalItems.value === 0) return 0;
    return (page.value - 1) * itemsPerPage.value + 1;
});
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
}

const filterableColumns = ref([
    { text: "予約開始日(から)", value: "start_at_from", type: "date" },
    { text: "予約開始日(まで)", value: "start_at_to", type: "date" },
    { text: "予約者番号", value: "booker_number", type: "number" },
    { text: "顧客名", value: "booker_name", type: "text" },
    { text: "メニュー", value: "menu_id", type: "select", items: props.menus.map(m => ({ text: m.name, value: m.id })) }, // int value
    { text: "担当スタッフ", value: "assigned_staff_id", type: "select", items: props.staffs.map(s => ({ text: s.name, value: s.id })) }, // int value
    { 
        text: "ステータス", 
        value: "status", 
        type: "select", 
        items: [
            { text: "確定", value: "confirmed" },
            { text: "保留", value: "pending" },
            { text: "キャンセル", value: "cancelled" },
        ] 
    },
    { 
        text: "予約経路", 
        value: "booking_channel", 
        type: "select", 
        items: [
            { text: "Web", value: "web" },
            { text: "手動", value: "manual" },
        ] 
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
    // Also remove from active filters immediately? Or wait for apply?
    // Pattern implies active filters drive the view, so we need to remove from active too if it was active.
    // Actually pattern calls removeFilter -> filters.splice -> applyFilters(false).
    applyFilters(false);
};

const activeFiltersText = computed(() => {
    return activeFilters.value.map((f) => {
        const column = filterableColumns.value.find(
            (c) => c.value === f.column
        );
        let displayValue = f.value;
        if (column?.type === 'select' && column.items) {
            const selectedItem = column.items.find((i: any) => i.value == f.value); // loose check for string/int
            if (selectedItem) displayValue = selectedItem.text;
        }

        return {
            id: f.id,
            text: column ? column.text : "",
            value: displayValue,
        };
    });
});

const applyFilters = (shouldCloseDialog = true) => {
    // Sync filters to activeFilters
    activeFilters.value = JSON.parse(
        JSON.stringify(filters.value.filter((f) => f.column && f.value !== null && f.value !== ""))
    );
    // Also sync activeFilters back to filters to keep them capable of being edited
    filters.value = JSON.parse(JSON.stringify(activeFilters.value));

    if (shouldCloseDialog) {
        filterDialog.value = false;
    }
    page.value = 1;
    // Trigger load
    loadItems({
        page: page.value,
        itemsPerPage: itemsPerPage.value,
        sortBy: [], // Sort is handled by activeSort
        groupBy: [],
        search: ''
    });
};

// --- Sorting ---
interface Sort {
    column: string | null;
    order: "asc" | "desc" | null;
}

const sortableColumns = ref([
    { text: "予約日時", value: "start_at" },
    { text: "予約者番号", value: "booker_number" },
    { text: "顧客名", value: "booker_name" },
    { text: "ステータス", value: "status" },
    { text: "合計料金", value: "total_price" },
    { text: "予約経路", value: "booking_channel" },
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
        groupBy: [],
        search: ''
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
        groupBy: [],
        search: ''
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
        const urlSortOrder = urlParams.get("sort_order") as "asc" | "desc" | null;
        if (urlSortBy && urlSortOrder) {
            sortBy.value = { column: urlSortBy, order: urlSortOrder };
            activeSort.value = { column: urlSortBy, order: urlSortOrder };
        }

        // フィルタの復元
        const tempFilters: Filter[] = [];
        urlParams.forEach((value, key) => {
            // Ignore pagination/sort keys
            if (['page', 'per_page', 'sort_by', 'sort_order'].includes(key)) return;

            const columnDef = filterableColumns.value.find(
                (c) => c.value === key
            );
            if (columnDef) {
                tempFilters.push({
                    id: Date.now() + Math.random(),
                    column: key,
                    value: value,
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
    } else {
        // Default sort (should match backend default or explicit)
        // Backend defaults to start_at desc if not provided.
    }

    // 動的にフィルタ条件をparamsに追加
    activeFilters.value.forEach((filter) => {
        if (filter.column && filter.value !== null && filter.value !== "") {
             params.append(filter.column, filter.value);
        }
    });

    const apiUrl = `/owner/api/shops/${props.shop.slug}/bookings?${params.toString()}`;
    history.pushState(null, "", `/owner/shops/${props.shop.slug}/bookings?${params.toString()}`);

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
    { title: "予約日時", key: "start_at", sortable: false },
    { title: "予約者番号", key: "booker_number", sortable: false },
    { title: "顧客名", key: "booker_name", sortable: false },
    { title: "メニュー", key: "menu_name", sortable: false }, // API should return menu_name
    { title: "担当スタッフ", key: "assigned_staff_name", sortable: false }, // API should return assigned_staff_name
    { title: "ステータス", key: "status", sortable: false },
    { title: "合計料金", key: "total_price", sortable: false },
    { title: "予約経路", key: "booking_channel", sortable: false },
    { title: "操作", key: "actions", sortable: false },
];

// --- Helpers ---
const formatDateTime = (dateStr: string) => {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleString('ja-JP', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};

const formatCurrency = (amount: number) => {
    if (amount === null || amount === undefined) return "";
    return amount.toLocaleString() + "円";
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'confirmed': return '確定';
        case 'pending': return '保留';
        case 'cancelled': return 'キャンセル';
        default: return status;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'confirmed': return 'success';
        case 'pending': return 'warning';
        case 'cancelled': return 'error';
        default: return 'grey';
    }
};

const getChannelText = (channel: string) => {
    switch (channel) {
        case 'web': return 'Web';
        case 'manual': return '手動';
        default: return channel;
    }
};

</script>
