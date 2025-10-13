<template>
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
                        }"
                    >
                        <span>契約詳細</span>
                        <v-btn
                            :href="editUrl"
                            color="primary"
                            :class="{ 'mt-2': smAndDown }"
                        >
                            契約を編集する
                        </v-btn>
                    </v-card-title>
                    <v-divider></v-divider>

                    <v-card-text>
                        <v-list-item>
                            <v-list-item-title>契約ID</v-list-item-title>
                            <v-list-item-subtitle>{{ contract.id }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title>契約名</v-list-item-title>
                            <v-list-item-subtitle>{{ contract.name }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item v-if="contract.user">
                            <v-list-item-title>PublicID</v-list-item-title>
                            <v-list-item-subtitle>{{ contract.user.public_id }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title>店舗上限数</v-list-item-title>
                            <v-list-item-subtitle>{{ contract.max_shops }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title>契約ステータス</v-list-item-title>
                            <v-list-item-subtitle>{{ contract.status }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title>契約開始日</v-list-item-title>
                            <v-list-item-subtitle>{{ new Date(contract.start_date).toLocaleDateString() }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title>契約終了日</v-list-item-title>
                            <v-list-item-subtitle>{{ new Date(contract.end_date).toLocaleDateString() }}</v-list-item-subtitle>
                        </v-list-item>
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
                        <v-list-item>
                            <v-list-item-title>申込ID</v-list-item-title>
                            <v-list-item-subtitle>{{ contract.application.id }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title>お客様名称</v-list-item-title>
                            <v-list-item-subtitle>{{ contract.application.customer_name }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title>お客様メールアドレス</v-list-item-title>
                            <v-list-item-subtitle>{{ contract.application.email }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title>申込日時</v-list-item-title>
                            <v-list-item-subtitle>{{ new Date(contract.application.created_at).toLocaleString() }}</v-list-item-subtitle>
                        </v-list-item>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useDisplay } from 'vuetify';

interface User {
    id: number;
    public_id: string;
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

const editUrl = computed(() => `/admin/contracts/${props.contract.id}/edit`);

</script>
