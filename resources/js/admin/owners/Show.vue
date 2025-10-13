<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn href="/admin/owners" prepend-icon="mdi-arrow-left">
                    オーナー一覧へ戻る
                </v-btn>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card class="mb-4">
                    <v-card-title class="d-flex justify-space-between align-center">
                        <span>オーナー詳細</span>
                        <v-btn :href="editUrl" color="primary">オーナー情報を編集する</v-btn>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-card-text>
                        <v-list density="compact">
                            <v-list-item>
                                <v-list-item-title>Public ID</v-list-item-title>
                                <v-list-item-subtitle>{{ owner.user.public_id }}</v-list-item-subtitle>
                            </v-list-item>
                            <v-list-item>
                                <v-list-item-title>オーナー名</v-list-item-title>
                                <v-list-item-subtitle>{{ owner.name }}</v-list-item-subtitle>
                            </v-list-item>
                            <v-list-item>
                                <v-list-item-title>登録日時</v-list-item-title>
                                <v-list-item-subtitle>{{ new Date(owner.created_at).toLocaleString() }}</v-list-item-subtitle>
                            </v-list-item>
                        </v-list>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Contracts List -->
        <v-row>
            <v-col cols="12">
                <v-card class="mb-4">
                    <v-card-title>契約一覧</v-card-title>
                    <v-divider></v-divider>
                    <v-card-text>
                        <v-data-table
                            :headers="contractHeaders"
                            :items="owner.contracts"
                            v-if="owner.contracts.length > 0"
                            density="compact"
                            hide-default-footer
                        >
                            <template v-slot:item.status="{ item }">
                                <v-chip :color="item.status === 'active' ? 'green' : 'grey'" size="small">
                                    {{ item.status === 'active' ? '有効' : '無効' }}
                                </v-chip>
                            </template>
                            <template v-slot:item.period="{ item }">
                                {{ new Date(item.start_date).toLocaleDateString() }} ~ {{ new Date(item.end_date).toLocaleDateString() }}
                            </template>
                            <template v-slot:item.actions="{ item }">
                                <v-btn :href="`/admin/contracts/${item.id}`" size="small" color="info" variant="tonal">詳細</v-btn>
                            </template>
                        </v-data-table>
                        <p v-else>このオーナーには契約がありません。</p>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Shops List -->
        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title>店舗一覧</v-card-title>
                    <v-divider></v-divider>
                    <v-card-text>
                        <v-data-table
                            :headers="shopHeaders"
                            :items="owner.shops"
                            v-if="owner.shops.length > 0"
                            density="compact"
                            hide-default-footer
                        >
                        </v-data-table>
                        <p v-else>このオーナーには店舗がありません。</p>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { VDataTable } from 'vuetify/components';

// --- Interfaces ---
interface User {
    id: number;
    public_id: string;
}

interface Contract {
    id: number;
    name: string;
    status: string;
    start_date: string;
    end_date: string;
}

interface Shop {
    id: number;
    slug: string;
    name: string;
}

interface Owner {
    id: number;
    name: string;
    created_at: string;
    user: User;
    contracts: Contract[];
    shops: Shop[];
}

const props = defineProps<{
    owner: Owner;
}>();

const editUrl = computed(() => `/admin/owners/${props.owner.user.public_id}/edit`);

const contractHeaders = [
    { title: '契約ID', key: 'id', sortable: false },
    { title: '契約名', key: 'name', sortable: false },
    { title: 'ステータス', key: 'status', sortable: false },
    { title: '契約期間', key: 'period', sortable: false },
    { title: '操作', key: 'actions', align: 'end', sortable: false },
];

const shopHeaders = [
    { title: '店舗ID', key: 'id', sortable: false },
    { title: '店舗Slug', key: 'slug', sortable: false },
    { title: '店舗名', key: 'name', sortable: false },
];

</script>
