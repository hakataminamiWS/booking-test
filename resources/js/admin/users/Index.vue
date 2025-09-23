<template>
    <v-container>
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>オーナー権限設定</span>
            </v-card-title>

            <v-card-text>
                <div class="mb-4">
                    <v-row>
                        <v-col cols="12" md="6">
                            <v-text-field
                                name="public_id"
                                label="Public IDで検索"
                                v-model="publicIdModel"
                                hide-details
                                dense
                                @keydown.enter="manualFetch"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="2">
                            <v-btn
                                type="button"
                                color="primary"
                                @click="manualFetch"
                                >検索</v-btn
                            >
                        </v-col>
                    </v-row>
                </div>

                <v-pagination
                    v-model="page"
                    :length="totalPages"
                    :total-visible="5"
                ></v-pagination>

                <div class="mt-2 mb-2">全 {{ totalItems }} 件</div>

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
                    class="elevation-1"
                >
                    <template v-slot:item.public_id="{ item }">
                        <a :href="`/admin/users/${item.public_id}`">{{
                            item.public_id
                        }}</a>
                    </template>
                    <template v-slot:item.is_owner="{ item }">
                        <v-chip :color="item.is_owner ? 'primary' : ''" dark>
                            {{ item.is_owner ? "Owner" : "User" }}
                        </v-chip>
                    </template>
                    <template v-slot:item.actions="{ item }">
                        <v-btn
                            :href="`/admin/users/${item.public_id}/edit`"
                            small
                            color="primary"
                        >
                            編集
                        </v-btn>
                    </template>
                </v-data-table-server>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<script setup lang="ts">
import { ref } from "vue";
import type { VDataTableServer } from "vuetify/components";
import axios from "axios";

type Options = InstanceType<typeof VDataTableServer>["$props"]["options"];

// --- URLパラメータから初期状態を決定 ---
const urlParams = new URLSearchParams(window.location.search);
const sortByFromUrl = urlParams.get("sort_by");
const initialSortBy = sortByFromUrl
    ? [{ key: sortByFromUrl, order: urlParams.get("sort_order") || "asc" }]
    : [];

// --- コンポーネントのリアクティブな状態を定義 ---
const serverItems = ref<any[]>([]);
const loading = ref(false);
const totalItems = ref(0);
const totalPages = ref(0);
const page = ref(Number(urlParams.get("page")) || 1);
const itemsPerPage = ref(Number(urlParams.get("per_page")) || 20);
const publicIdModel = ref(urlParams.get("public_id") || "");
const sortBy = ref<any[]>(initialSortBy);
const options = ref<Options>({} as Options); // テーブルの現在の状態を保持

const headers = [
    { title: "Public ID", value: "public_id", key: "public_id" },
    { title: "役割", value: "is_owner", key: "is_owner", sortable: false },
    { title: "登録日時", value: "created_at", key: "created_at" },
    { title: "操作", value: "actions", sortable: false },
];

const loadItems = async (newOptions: Options) => {
    options.value = newOptions; // 親スコープのoptionsを更新
    page.value = newOptions.page; // ページネーションの見た目をデータと同期
    loading.value = true;

    const params = new URLSearchParams();
    params.append("page", String(newOptions.page));
    params.append("per_page", String(newOptions.itemsPerPage));

    if (newOptions.sortBy.length) {
        params.append("sort_by", newOptions.sortBy[0].key);
        params.append("sort_order", newOptions.sortBy[0].order);
    }

    if (publicIdModel.value) {
        params.append("public_id", publicIdModel.value);
    }

    const queryString = params.toString();
    const url = `/admin/users?${queryString}`;
    const apiUrl = `/api/admin/users?${queryString}`;

    if (window.location.search !== `?${queryString}`) {
        history.pushState(null, "", url);
    }

    try {
        const response = await axios.get(apiUrl);
        serverItems.value = response.data.data;
        totalItems.value = response.data.total;
        totalPages.value = response.data.last_page;
    } catch (error) {
        console.error("Error fetching users:", error);
    } finally {
        loading.value = false;
    }
};

const manualFetch = () => {
    // 検索時は1ページ目に戻すため、現在のoptionsを元にpageを1にしてloadItemsを直接呼び出す
    loadItems({ ...options.value, page: 1 });
};
</script>
