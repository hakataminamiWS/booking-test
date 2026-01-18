<template>
    <AdminLayout current-page="contract-applications">
        <v-container>
            <!-- Top Link -->
            <v-row>
                <v-col cols="12">
                    <v-btn
                           :href="'/admin/contract-applications'"
                           prepend-icon="mdi-arrow-left">
                        契約申し込み一覧へ戻る
                    </v-btn>
                </v-col>
            </v-row>

            <!-- Contents -->
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
                            <span>契約申し込み詳細</span>
                            <v-btn
                                   :href="editUrl"
                                   color="primary"
                                   :class="{ 'mt-2': smAndDown }">
                                申し込みステータスを編集する
                            </v-btn>
                        </v-card-title>
                        <v-divider></v-divider>

                        <v-card-text>
                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">申込ID</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ contractApplication.id }}</v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">お客様名称</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ contractApplication.customer_name
                                    }}</v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">メールアドレス</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ contractApplication.email }}</v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">申し込みステータス</v-card-title>
                                <v-card-text class="pa-0 text-body-1">
                                    <v-chip :color="getAppStatusColor(contractApplication.status)" size="small">
                                        {{ getAppStatusText(contractApplication.status) }}
                                    </v-chip>
                                </v-card-text>
                            </v-card>

                            <v-card variant="text" class="mb-4">
                                <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">申込日時</v-card-title>
                                <v-card-text class="pa-0 text-body-1">{{ new
                                    Date(contractApplication.created_at).toLocaleString() }}</v-card-text>
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

const { getAppStatusText, getAppStatusColor } = useContractStatus();

interface User {
    id: number;
}

interface ContractApplication {
    id: number;
    customer_name: string;
    email: string;
    status: string;
    created_at: string;
    user: User | null;
}

const props = defineProps<{
    contractApplication: ContractApplication;
}>();

const { smAndDown } = useDisplay();

const editUrl = computed(
    () => `/admin/contract-applications/${props.contractApplication.id}/edit`
);
</script>
