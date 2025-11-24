<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn :href="'/admin/contracts'" prepend-icon="mdi-arrow-left">
                    契約一覧へ
                </v-btn>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title>
                        <span>契約新規作成</span>
                    </v-card-title>
                    <v-card-text>
                        <v-alert
                            v-if="props.errors.length > 0"
                            type="error"
                            class="mb-4"
                        >
                            <ul>
                                <li v-for="(error, i) in props.errors" :key="i">
                                    {{ error }}
                                </li>
                            </ul>
                        </v-alert>
                        <!-- Application Info -->
                        <v-card variant="outlined" class="mb-6">
                            <v-card-item>
                                <v-card-title>申し込み情報</v-card-title>
                            </v-card-item>
                            <v-list-item
                                :title="`申込 ID: ${application.id}`"
                            ></v-list-item>
                            <v-list-item
                                :title="`お客様名称: ${application.customer_name}`"
                            ></v-list-item>
                            <v-list-item
                                :title="`メールアドレス: ${application.email}`"
                            ></v-list-item>
                            <v-list-item
                                :title="`申し込みステータス: ${application.status}`"
                            ></v-list-item>
                        </v-card>

                        <!-- Contract Form -->
                        <form method="POST" action="/admin/contracts">
                            <input
                                type="hidden"
                                name="application_id"
                                :value="application.id"
                            />
                            <input
                                type="hidden"
                                name="user_id"
                                :value="application.user.id"
                            />
                            <input
                                type="hidden"
                                name="_token"
                                :value="csrfToken"
                            />

                            <v-text-field
                                v-model="form.name"
                                name="name"
                                label="契約名"
                                required
                                :rules="[rules.required]"
                            ></v-text-field>

                            <v-text-field
                                v-model="form.max_shops"
                                @update:model-value="
                                    form.max_shops = formatNumericInput($event)
                                "
                                name="max_shops"
                                label="店舗上限数"
                                :rules="[rules.required, rules.numeric]"
                                inputmode="numeric"
                                required
                            ></v-text-field>

                            <v-select
                                v-model="form.status"
                                name="status"
                                :items="['active', 'expired']"
                                label="契約ステータス"
                                required
                                :rules="[rules.required]"
                            ></v-select>

                            <v-text-field
                                v-model="form.start_date"
                                name="start_date"
                                label="契約開始日"
                                type="date"
                                required
                                :rules="[rules.required]"
                            ></v-text-field>

                            <v-text-field
                                v-model="form.end_date"
                                name="end_date"
                                label="契約終了日"
                                type="date"
                                required
                                :rules="[rules.required]"
                            ></v-text-field>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn
                                    type="submit"
                                    color="primary"
                                    :disabled="application.status !== 'pending'"
                                    @click="validateAndSubmit"
                                >
                                    契約を作成する
                                </v-btn>
                            </v-card-actions>
                        </form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { formatNumericInput } from "@/composables/useNumericInput";

const props = defineProps<{
    application: any;
    errors: string[];
}>();

const csrfToken = ref("");

const form = ref({
    application_id: props.application.id,
    user_id: props.application.user.id,
    name: "",
    max_shops: 1,
    status: "active",
    start_date: new Date().toISOString().slice(0, 10),
    end_date: new Date(new Date().setFullYear(new Date().getFullYear() + 1))
        .toISOString()
        .slice(0, 10),
});

onMounted(() => {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        csrfToken.value = token.getAttribute("content") || "";
    }
});

const rules = {
    required: (value: any) => !!(value || value === 0) || "必須項目です。",
    numeric: (value: string) =>
        /^(0|[1-9][0-9]*)$/.test(value) || "半角数字で入力してください。",
};

const validateAndSubmit = (event: Event) => {
    const fieldsToValidate = {
        name: form.value.name,
        max_shops: form.value.max_shops,
        status: form.value.status,
        start_date: form.value.start_date,
        end_date: form.value.end_date,
    };

    for (const [key, value] of Object.entries(fieldsToValidate)) {
        if (rules.required(value) !== true) {
            event.preventDefault();
            return;
        }
        if (key === "max_shops") {
            if (rules.numeric(String(value)) !== true) {
                event.preventDefault();
                return;
            }
        }
    }
};
</script>
