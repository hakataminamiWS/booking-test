<template>
    <v-container>
        <v-card max-width="600" class="mx-auto">
            <v-card-title class="text-h5">
                サービス利用契約のお申し込み
            </v-card-title>
            <v-card-text>
                <v-alert
                    v-if="props.errors && props.errors.length > 0"
                    type="error"
                    variant="tonal"
                    class="mb-4"
                >
                    <ul>
                        <li v-for="error in props.errors" :key="error">{{ error }}</li>
                    </ul>
                </v-alert>

                <p class="mb-6">
                    サービスの利用をご希望いただき、誠にありがとうございます。<br />
                    以下の内容をご確認の上、お申し込みください。
                </p>
                <form :action="formAction" method="POST">
                    <input type="hidden" name="_token" :value="csrfToken" />

                    <v-text-field
                        label="ユーザーID"
                        :model-value="props.publicId"
                        readonly
                        variant="outlined"
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

                    <v-text-field
                        v-model="email"
                        name="email"
                        label="メールアドレス"
                        type="email"
                        variant="outlined"
                        required
                        class="mb-4"
                    ></v-text-field>

                    <v-btn type="submit" color="primary" block size="large">
                        上記の内容で申し込む
                    </v-btn>
                </form>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<script setup lang="ts">
import { ref } from "vue";

interface Props {
    publicId: string;
    errors?: string[];
}

const props = defineProps<Props>();

const customerName = ref("");
const email = ref("");
const csrfToken = (
    document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
).content;
const formAction = "/contract-applications";
</script>
