<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn
                    :href="'/admin/contract-applications'"
                    prepend-icon="mdi-arrow-left"
                >
                    契約申し込み一覧へ戻る
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
                        <span>契約申し込み詳細</span>
                        <v-btn
                            :href="editUrl"
                            color="primary"
                            :class="{ 'mt-2': smAndDown }"
                        >
                            申し込みステータスを編集する
                        </v-btn>
                    </v-card-title>
                    <v-divider></v-divider>

                    <v-card-text>
                        <v-list-item>
                            <v-list-item-title>申込ID</v-list-item-title>
                            <v-list-item-subtitle>{{
                                contractApplication.id
                            }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title>お客様名称</v-list-item-title>
                            <v-list-item-subtitle>{{
                                contractApplication.customer_name
                            }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title
                                >メールアドレス</v-list-item-title
                            >
                            <v-list-item-subtitle>{{
                                contractApplication.email
                            }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title
                                >申し込みステータス</v-list-item-title
                            >
                            <v-list-item-subtitle>{{
                                contractApplication.status
                            }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title>申込日時</v-list-item-title>
                            <v-list-item-subtitle>{{
                                new Date(
                                    contractApplication.created_at
                                ).toLocaleString()
                            }}</v-list-item-subtitle>
                        </v-list-item>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useDisplay } from "vuetify";

interface User {
    id: number;
    public_id: string;
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
