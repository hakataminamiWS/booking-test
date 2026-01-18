<template>
    <AdminLayout current-page="contracts">
        <v-container>
            <v-row>
                <v-col cols="12">
                    <v-btn :href="'/admin/contracts'" prepend-icon="mdi-arrow-left">
                        契約一覧へ戻る
                    </v-btn>
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
                            <span>契約詳細</span>
                            <v-btn
                                   :href="editUrl"
                                   color="primary"
                                   :class="{ 'mt-2': smAndDown }">
                                契約を編集する
                            </v-btn>
                        </v-card-title>
                        <v-divider></v-divider>

                        <v-card-text>
                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">契約ID</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ contract.id }}</v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">契約名</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ contract.name }}</v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">店舗上限数</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ contract.max_shops }}</v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">契約ステータス</v-card-title>
                                <v-card-text class="pa-0 text-body-1">
                                    <v-chip :color="getContractStatusColor(contract.status)" size="small">
                                        {{ getContractStatusText(contract.status) }}
                                    </v-chip>
                                </v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">契約開始日</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ new
                                    Date(contract.start_date).toLocaleDateString()
                                    }}</v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">契約終了日</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ new
                                    Date(contract.end_date).toLocaleDateString()
                                    }}</v-card-text>
                            </v-card>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <v-row v-if="contract.application">
                <v-col cols="12">
                    <v-card>
                        <v-card-title>契約申し込み情報</v-card-title>
                        <v-divider></v-divider>
                        <v-card-text>
                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">申込ID</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ contract.application.id }}</v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">お客様名称</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ contract.application.customer_name
                                    }}</v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">お客様メールアドレス</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ contract.application.email }}</v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">申込日時</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ new
                                    Date(contract.application.created_at).toLocaleString() }}</v-card-text>
                            </v-card>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useDisplay } from "vuetify";
import AdminLayout from "@/components/admin/AdminLayout.vue";
import { useContractStatus } from "@/composables/useContractStatus";

interface User {
    id: number;
}

interface ContractApplication {
    id: number;
    customer_name: string;
    email: string;
    created_at: string;
}

interface Contract {
    id: number;
    name: string;
    max_shops: number;
    status: string;
    start_date: string;
    end_date: string;
    user: User | null;
    application: ContractApplication | null;
}

const props = defineProps<{
    contract: Contract;
}>();

const { smAndDown } = useDisplay();
const { getContractStatusText, getContractStatusColor } = useContractStatus();

const editUrl = computed(() => `/admin/contracts/${props.contract.id}/edit`);
</script>
