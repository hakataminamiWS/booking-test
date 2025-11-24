<template>
    <v-container>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>予約一覧</span>
                <v-btn
                    color="primary"
                    :href="route('owner.shops.bookings.create', { shop: props.shop.slug })"
                >
                    手動で予約を登録する
                </v-btn>
            </v-card-title>
            <v-card-text>
                <!-- ControlBar -->
                <v-row class="align-center mb-2" dense>
                    <v-col cols="auto">
                        <v-btn variant="tonal" @click="filterDialog = true" append-icon="mdi-filter-variant">
                            絞り込み
                        </v-btn>
                    </v-col>
                    <v-col cols="auto">
                        <v-btn variant="tonal" @click="sortDialog = true" prepend-icon="mdi-sort">
                            並び替え
                        </v-btn>
                    </v-col>
                    <v-spacer></v-spacer>
                    <v-col cols="auto" class="text-body-2">
                        全 {{ totalItems }} 件中 {{ from }} - {{ to }} 件表示
                    </v-col>
                    <v-col cols="auto">
                        <v-pagination v-model="page" :length="totalPages" :total-visible="5" density="compact"></v-pagination>
                    </v-col>
                </v-row>

                <!-- Applied Filters & Sort Chips -->
                <v-row dense>
                    <v-col cols="12">
                        <v-chip v-for="filter in activeFiltersText" :key="filter.id" class="mr-2 mb-2" closable @click:close="removeFilter(filter.id)">
                            {{ filter.text }}: {{ filter.valueText || filter.value }}
                        </v-chip>
                        <v-chip v-if="sortChipText" class="mr-2 mb-2" closable @click:close="removeSort">
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
                    <template v-slot:item.start_at="{ item }">
                        {{ new Date(item.start_at).toLocaleString() }}
                    </template>
                    <template v-slot:item.total_price="{ item }">
                        {{ item.total_price.toLocaleString() }} 円
                    </template>
                    <template v-slot:item.actions="{ item }">
                        <v-btn color="primary" size="small" :href="route('owner.shops.bookings.show', { shop: props.shop.slug, booking: item.id })">
                            詳細
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
                    <v-row v-for="filter in filters" :key="filter.id" align="center">
                        <v-col cols="4">
                            <v-select v-model="filter.column" :items="filterableColumns" item-title="text" item-value="value" label="対象列" dense hide-details></v-select>
                        </v-col>
                        <v-col cols="7">
                            <v-text-field v-if="getColumnType(filter.column) === 'text'" v-model="filter.value" label="値" dense hide-details></v-text-field>
                            <v-text-field v-if="getColumnType(filter.column) === 'date'" v-model="filter.value" label="値" type="date" dense hide-details></v-text-field>
                            <v-select v-if="getColumnType(filter.column) === 'select'" v-model="filter.value" :items="getColumnItems(filter.column)" label="値" dense hide-details></v-select>
                        </v-col>
                        <v-col cols="1">
                            <v-btn icon size="small" @click="removeFilter(filter.id)"><v-icon>mdi-close</v-icon></v-btn>
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
                            <v-select v-model="sortBy.column" :items="sortableColumns" item-title="text" item-value="value" label="対象列" dense hide-details></v-select>
                        </v-col>
                        <v-col cols="6">
                            <v-select v-model="sortBy.order" :items="[{ text: '昇順', value: 'asc' }, { text: '降順', value: 'desc' }]" item-title="text" item-value="value" label="順序" dense hide-details></v-select>
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

type Options = InstanceType<typeof VDataTableServer>["$props"]["options"];
type Headers = InstanceType<typeof VDataTableServer>["$props"]["headers"];

const props = defineProps({
    shop: { type: Object, required: true },
    menus: { type: Array, required: true },
    staffs: { type: Array, required: true },
});

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

interface Filter {
    id: number;
    column: string | null;
    value: string | null;
}

const bookingStatusItems = [
    { title: '保留', value: 'pending' },
    { title: '確定', value: 'confirmed' },
    { title: 'キャンセル', value: 'cancelled' },
];

const bookingChannelItems = [
    { title: 'Web予約', value: 'web' },
    { title: '手動登録', value: 'manual' },
];

const filterableColumns = ref([
    { text: "予約日(から)", value: "start_at_from", type: "date" },
    { text: "予約日(まで)", value: "start_at_to", type: "date" },
    { text: "顧客名", value: "booker_name", type: "text" },
    { text: "メニュー", value: "menu_id", type: "select", items: props.menus.map(m => ({ title: m.name, value: m.id })) },
    { text: "担当スタッフ", value: "assigned_staff_id", type: "select", items: props.staffs.map(s => ({ title: s.name, value: s.id })) },
    { text: "ステータス", value: "status", type: "select", items: bookingStatusItems },
    { text: "予約経路", value: "booking_channel", type: "select", items: bookingChannelItems },
]);

const filters = ref<Filter[]>([]);
const activeFilters = ref<Filter[]>([]);

const getColumnType = (columnValue: string | null) => filterableColumns.value.find((c) => c.value === columnValue)?.type || "text";
const getColumnItems = (columnValue: string | null) => filterableColumns.value.find((c) => c.value === columnValue)?.items || [];

const addFilter = () => { filters.value.push({ id: Date.now(), column: null, value: null }); };
const removeFilter = (id: number) => {
    const index = filters.value.findIndex((f) => f.id === id);
    if (index > -1) filters.value.splice(index, 1);
    applyFilters(false);
};

const activeFiltersText = computed(() => {
    return activeFilters.value.map((f) => {
        const column = filterableColumns.value.find((c) => c.value === f.column);
        let valueText = f.value;
        if (column?.type === 'select') {
            const item = column.items.find(i => i.value == f.value);
            valueText = item ? item.title : f.value;
        }
        return { id: f.id, text: column ? column.text : "", value: f.value, valueText };
    });
});

const applyFilters = (shouldCloseDialog = true) => {
    activeFilters.value = JSON.parse(JSON.stringify(filters.value.filter((f) => f.column && f.value)));
    if (shouldCloseDialog) filterDialog.value = false;
    page.value = 1;
    loadItems({ page: page.value, itemsPerPage: itemsPerPage.value, sortBy: [] });
};

interface Sort { column: string | null; order: "asc" | "desc" | null; }

const sortableColumns = ref([
    { text: "予約日時", value: "start_at" },
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
    const column = sortableColumns.value.find((c) => c.value === activeSort.value.column);
    if (!column) return null;
    const orderText = activeSort.value.order === "asc" ? "昇順" : "降順";
    return `並び替え: ${column.text} (${orderText})`;
});

const loadItems = async (options: Options) => {
    loading.value = true;

    if (isInitialLoad) {
        const urlParams = new URLSearchParams(window.location.search);
        const urlPage = urlParams.get("page");
        if (urlPage) page.value = parseInt(urlPage, 10);

        const urlSortBy = urlParams.get("sort_by");
        const urlSortOrder = urlParams.get("sort_order") as "asc" | "desc" | null;
        if (urlSortBy && urlSortOrder) {
            sortBy.value = { column: urlSortBy, order: urlSortOrder };
            activeSort.value = { column: urlSortBy, order: urlSortOrder };
        }

        const tempFilters: Filter[] = [];
        urlParams.forEach((value, key) => {
            if (filterableColumns.value.some(c => c.value === key)) {
                tempFilters.push({ id: Date.now() + Math.random(), column: key, value });
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
        if (filter.column && filter.value) {
            params.append(filter.column, filter.value);
        }
    });

    const apiUrl = `/owner/api/shops/${props.shop.slug}/bookings?${params.toString()}`;
    history.pushState(null, "", `?${params.toString()}`);

    try {
        // TODO: API実装後にコメントを外す
        // const response = await axios.get(apiUrl);
        // serverItems.value = response.data.data;
        // totalItems.value = response.data.total;
        serverItems.value = []; // モックデータ
        totalItems.value = 0;   // モックデータ
    } catch (error) {
        console.error("Failed to load items:", error);
        serverItems.value = [];
        totalItems.value = 0;
    } finally {
        loading.value = false;
    }
};

const headers: Headers = [
    { title: "予約日時", key: "start_at", sortable: false },
    { title: "顧客名", key: "booker_name", sortable: false },
    { title: "メニュー", key: "menu_name", sortable: false },
    { title: "担当スタッフ", key: "assigned_staff_name", sortable: false },
    { title: "ステータス", key: "status", sortable: false },
    { title: "合計料金", key: "total_price", sortable: false },
    { title: "予約経路", key: "booking_channel", sortable: false },
    { title: "操作", key: "actions", sortable: false, align: 'end' },
];
</script>