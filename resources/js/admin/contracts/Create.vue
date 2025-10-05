<template>
    <v-container>
        <v-card class="mb-4">
            <v-card-text class="d-flex justify-end">
                <v-btn
                    color="primary"
                    variant="text"
                    prepend-icon="mdi-format-list-bulleted"
                    href="/admin/contracts"
                >
                    契約一覧へ
                </v-btn>
            </v-card-text>
        </v-card>

        <v-card>
            <v-card-title>
                <span>契約新規作成</span>
            </v-card-title>
            <v-card-text>
                <!-- Application Info -->
                <v-card variant="outlined" class="mb-6">
                    <v-card-item>
                        <v-card-title>申し込み情報</v-card-title>
                    </v-card-item>
                    <v-list-item :title="`申込 ID: ${application.id}`"></v-list-item>
                    <v-list-item :title="`Public ID: ${application.user.public_id}`"></v-list-item>
                    <v-list-item :title="`お客様名称: ${application.customer_name}`"></v-list-item>
                    <v-list-item :title="`メールアドレス: ${application.email}`"></v-list-item>
                    <v-list-item :title="`申し込みステータス: ${application.status}`"></v-list-item>
                </v-card>

                <!-- Contract Form -->
                <form method="POST" action="/admin/contracts">
                    <input type="hidden" name="application_id" :value="application.id">
                    <input type="hidden" name="user_id" :value="application.user.id">
                    <input type="hidden" name="_token" :value="csrfToken">

                    <v-text-field
                        v-model="form.name"
                        name="name"
                        label="契約名"
                        :error-messages="errors.name"
                        required
                    ></v-text-field>

                    <v-text-field
                        v-model.number="form.max_shops"
                        name="max_shops"
                        label="店舗上限数"
                        type="number"
                        :error-messages="errors.max_shops"
                        required
                    ></v-text-field>

                    <v-select
                        v-model="form.status"
                        name="status"
                        :items="['active', 'expired']"
                        label="契約ステータス"
                        :error-messages="errors.status"
                        required
                    ></v-select>

                    <v-text-field
                        v-model="form.start_date"
                        name="start_date"
                        label="契約開始日"
                        type="date"
                        :error-messages="errors.start_date"
                        required
                    ></v-text-field>

                    <v-text-field
                        v-model="form.end_date"
                        name="end_date"
                        label="契約終了日"
                        type="date"
                        :error-messages="errors.end_date"
                        required
                    ></v-text-field>

                    <v-btn
                        type="submit"
                        color="primary"
                        :disabled="application.status !== 'pending'"
                    >
                        契約を作成する
                    </v-btn>
                </form>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

const props = defineProps<{
    application: any;
    errors: Record<string, string[]>;
}>();

const csrfToken = ref('');

const form = ref({
    application_id: props.application.id,
    user_id: props.application.user.id,
    name: '',
    max_shops: 1,
    status: 'active',
    start_date: new Date().toISOString().slice(0, 10),
    end_date: new Date(new Date().setFullYear(new Date().getFullYear() + 1)).toISOString().slice(0, 10),
});

const errors = ref<Record<string, string[]>>({});

onMounted(() => {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        csrfToken.value = token.getAttribute('content') || '';
    }
    if (props.errors) {
        errors.value = props.errors;
    }
});
</script>
