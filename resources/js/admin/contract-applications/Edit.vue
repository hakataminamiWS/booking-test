<template>
    <v-container>
        <!-- Error Messages -->
        <v-row v-if="props.errors.length > 0">
            <v-col cols="12">
                <v-alert type="error" title="入力エラー">
                    <ul>
                        <li v-for="(error, i) in props.errors" :key="i">{{ error }}</li>
                    </ul>
                </v-alert>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title class="d-flex justify-space-between align-center">
                        <span>契約申し込み編集</span>
                        <v-btn :href="showUrl" prepend-icon="mdi-arrow-left">
                            申し込み詳細へ戻る
                        </v-btn>
                    </v-card-title>
                    <v-divider></v-divider>

                    <!-- Update Form -->
                    <form :action="updateUrl" method="POST">
                        <input type="hidden" name="_token" :value="props.csrfToken">
                        <input type="hidden" name="_method" value="PUT">

                        <v-card-text>
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-text-field label="申込ID" :model-value="props.contractApplication.id" readonly disabled></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field label="PublicID" :model-value="props.contractApplication.user?.public_id" readonly disabled></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field label="お客様名称" :model-value="props.contractApplication.customer_name" readonly disabled></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field label="メールアドレス" :model-value="props.contractApplication.email" readonly disabled></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-select name="status" label="申し込みステータス" v-model="form.status" :items="['pending', 'approved', 'rejected']"></v-select>
                                </v-col>
                            </v-row>
                        </v-card-text>

                        <v-card-actions class="pa-4">
                            <v-spacer></v-spacer>
                            <v-btn color="primary" type="submit">更新</v-btn>
                        </v-card-actions>
                    </form>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

// --- Interfaces ---
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
    csrfToken: string;
    errors: string[];
    oldInput: { [key: string]: any } | null;
}>();

// --- Form State ---
const form = ref({
    status: 'pending',
});

onMounted(() => {
    // Set initial form values from old input or prop
    form.value.status = props.oldInput?.status ?? props.contractApplication.status;
});

// --- URLs ---
const showUrl = computed(() => `/admin/contract-applications/${props.contractApplication.id}`);
const updateUrl = computed(() => `/admin/contract-applications/${props.contractApplication.id}`);

</script>
