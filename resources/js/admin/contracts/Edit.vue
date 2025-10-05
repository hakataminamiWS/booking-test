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
                        <span>契約編集</span>
                        <v-btn :href="showUrl" prepend-icon="mdi-arrow-left">
                            契約詳細へ戻る
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
                                    <v-text-field label="契約ID" :model-value="props.contract.id" readonly disabled></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field label="PublicID" :model-value="props.contract.user?.public_id" readonly disabled></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-text-field name="name" label="契約名" v-model="form.name"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field name="max_shops" label="店舗上限数" v-model="form.max_shops" type="number"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-select name="status" label="契約ステータス" v-model="form.status" :items="['active', 'expired']"></v-select>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field name="start_date" label="契約開始日" v-model="form.start_date" type="date"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field name="end_date" label="契約終了日" v-model="form.end_date" type="date"></v-text-field>
                                </v-col>
                            </v-row>
                        </v-card-text>

                        <v-card-actions class="pa-4">
                            <v-btn color="error" @click="deleteDialog = true">削除</v-btn>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" type="submit">更新</v-btn>
                        </v-card-actions>
                    </form>
                </v-card>
            </v-col>
        </v-row>

        <!-- Delete Dialog -->
        <v-dialog v-model="deleteDialog" max-width="600px">
            <v-card>
                <v-card-title class="text-h5">契約を削除しますか？</v-card-title>
                <v-card-text>
                    <p>この操作は元に戻せません。契約を削除するには、以下の契約名を入力してください。</p>
                    <p class="font-weight-bold text-center my-4">{{ props.contract.name }}</p>
                    <v-text-field
                        v-model="confirmationText"
                        label="契約名を入力"
                        outlined
                    ></v-text-field>
                    <v-alert type="warning" variant="outlined">
                        注意事項: 「契約申し込み情報」は新規契約作成時にのみ設定できます。もし関連付けを変更したい場合は、この契約を削除した後に、再度「契約申し込み一覧」から新規契約を作成してください。
                    </v-alert>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="deleteDialog = false">キャンセル</v-btn>
                    <form :action="deleteUrl" method="POST">
                         <input type="hidden" name="_token" :value="props.csrfToken">
                         <input type="hidden" name="_method" value="DELETE">
                        <v-btn
                            color="error"
                            type="submit"
                            :disabled="!isDeleteConfirmed"
                        >
                            削除を実行
                        </v-btn>
                    </form>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

// --- Interfaces ---
interface User {
    id: number;
    public_id: string;
}

interface Contract {
    id: number;
    name: string;
    max_shops: number;
    status: string;
    start_date: string;
    end_date: string;
    user: User | null;
}

const props = defineProps<{
    contract: Contract;
    csrfToken: string;
    errors: string[];
    oldInput: { [key: string]: any } | null;
}>();

// --- Form State ---
const form = ref({
    name: '',
    max_shops: 1,
    status: 'active',
    start_date: '',
    end_date: ''
});

onMounted(() => {
    // Set initial form values from old input or contract prop
    form.value.name = props.oldInput?.name ?? props.contract.name;
    form.value.max_shops = props.oldInput?.max_shops ?? props.contract.max_shops;
    form.value.status = props.oldInput?.status ?? props.contract.status;
    form.value.start_date = props.oldInput?.start_date ?? props.contract.start_date;
    form.value.end_date = props.oldInput?.end_date ?? props.contract.end_date;
});

// --- URLs ---
const showUrl = computed(() => `/admin/contracts/${props.contract.id}`);
const updateUrl = computed(() => `/admin/contracts/${props.contract.id}`);
const deleteUrl = computed(() => `/admin/contracts/${props.contract.id}`);

// --- Delete Dialog --- 
const deleteDialog = ref(false);
const confirmationText = ref('');
const isDeleteConfirmed = computed(() => confirmationText.value === props.contract.name);

</script>