<template>
    <v-container>
        <!-- Error Messages -->
        <v-row v-if="Object.keys(props.errors).length > 0">
            <v-col cols="12">
                <v-alert type="error" title="入力エラー">
                    <ul>
                        <li v-for="(error, key) in props.errors" :key="key">{{ error[0] }}</li>
                    </ul>
                </v-alert>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title class="d-flex justify-space-between align-center">
                        <span>オーナー情報編集</span>
                        <v-btn :href="showUrl" prepend-icon="mdi-arrow-left">
                            オーナー詳細へ戻る
                        </v-btn>
                    </v-card-title>
                    <v-divider></v-divider>

                    <!-- Update Form -->
                    <form :action="updateUrl" method="POST">
                        <input type="hidden" name="_token" :value="csrfToken">
                        <input type="hidden" name="_method" value="PUT">

                        <v-card-text>
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-text-field label="Public ID" :model-value="props.owner.user.public_id" readonly disabled></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field label="登録日時" :model-value="new Date(props.owner.created_at).toLocaleString()" readonly disabled></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-text-field name="name" label="オーナー名" v-model="form.name"></v-text-field>
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
                <v-card-title class="text-h5">オーナーを削除しますか？</v-card-title>
                <v-card-text>
                    <p>この操作により、ユーザーはオーナーとしての権限を失います。ユーザーアカウント自体は削除されません。操作を元に戻すことはできません。</p>
                    <p class="mt-4">オーナーを削除するには、以下のオーナー名を入力してください。</p>
                    <p class="font-weight-bold text-center my-4">{{ props.owner.name }}</p>
                    <v-text-field
                        v-model="confirmationText"
                        label="オーナー名を入力"
                        outlined
                    ></v-text-field>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="deleteDialog = false">キャンセル</v-btn>
                    <form :action="deleteUrl" method="POST">
                         <input type="hidden" name="_token" :value="csrfToken">
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

interface Owner {
    id: number;
    name: string;
    created_at: string;
    user: User;
}

const props = defineProps<{
    owner: Owner;
    errors: { [key: string]: string[] };
}>();

// --- Form State ---
const form = ref({
    name: '',
});

onMounted(() => {
    form.value.name = props.owner.name;
});

// --- CSRF Token ---
const csrfToken = computed(() => {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
});

// --- URLs ---
const showUrl = computed(() => `/admin/owners/${props.owner.user.public_id}`);
const updateUrl = computed(() => `/admin/owners/${props.owner.user.public_id}`);
const deleteUrl = computed(() => `/admin/owners/${props.owner.user.public_id}`);

// --- Delete Dialog ---
const deleteDialog = ref(false);
const confirmationText = ref('');
const isDeleteConfirmed = computed(() => confirmationText.value === props.owner.name);

</script>
