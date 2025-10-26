<template>
    <v-container>
        <v-row justify="center">
            <v-col cols="12" md="8">
                <v-card>
                    <v-card-title class="text-h5">
                        予約サービスのお申し込み
                    </v-card-title>

                    <v-card-text>
                        <v-alert
                            v-if="props.errors && props.errors.length > 0"
                            type="error"
                            variant="tonal"
                            class="mb-4"
                        >
                            <ul>
                                <li v-for="error in props.errors" :key="error">
                                    {{ error }}
                                </li>
                            </ul>
                        </v-alert>

                        <p class="mb-6">
                            予約サービスの利用をご希望いただき、誠にありがとうございます。<br />
                            以下の内容をご確認の上、お申し込みください。
                        </p>
                        <form :action="formAction" method="POST">
                            <input
                                type="hidden"
                                name="_token"
                                :value="csrfToken"
                            />

                            <input
                                type="hidden"
                                name="user_id"
                                :value="props.userId"
                            />

                            <v-text-field
                                :model-value="props.email"
                                name="email"
                                label="メールアドレス"
                                type="email"
                                variant="outlined"
                                readonly
                                class="mb-4"
                            ></v-text-field>

                            <v-text-field
                                v-model="customerName"
                                name="customer_name"
                                label="お客様名称"
                                placeholder="例: 株式会社〇〇 / 〇〇ストア"
                                variant="outlined"
                                required
                                class="mb-4"
                            ></v-text-field>

                            <v-btn
                                type="submit"
                                color="primary"
                                block
                                size="large"
                            >
                                上記の内容で申し込む
                            </v-btn>
                        </form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { ref } from "vue";

interface Props {
    userId: number;
    email: string;
    errors?: string[];
    oldInput?: { customer_name?: string; email?: string };
}

const props = defineProps<Props>();

const customerName = ref(props.oldInput?.customer_name || "");
const email = ref(props.oldInput?.email || props.email || "");
const csrfToken = (
    document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
).content;
const formAction = "/contract-applications";
</script>
