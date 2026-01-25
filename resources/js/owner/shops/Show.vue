<template>
    <OwnerLayout :shop="props.shop" currentPage="home">
        <v-container>
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
                            <span>店舗詳細</span>
                            <v-btn
                                   color="primary"
                                   :class="{ 'mt-2': smAndDown }"
                                   :href="`/owner/shops/${props.shop.slug}/edit`">
                                店舗詳細を編集する
                            </v-btn>
                        </v-card-title>
                        <v-divider></v-divider>
                        <v-card-text>
                            <v-table density="compact">
                                <tbody>
                                    <tr>
                                        <td>店舗名</td>
                                        <td>{{ props.shop.name }}</td>
                                    </tr>
                                    <tr>
                                        <td>店舗ID</td>
                                        <td>{{ props.shop.slug }}</td>
                                    </tr>
                                    <tr>
                                        <td>店舗メールアドレス</td>
                                        <td>
                                            <div class="d-flex align-center">
                                                <span>{{ props.shop.email }}</span>
                                                <v-btn
                                                       v-if="props.shop.email"
                                                       variant="text"
                                                       color="primary"
                                                       size="small"
                                                       class="ml-2"
                                                       @click="testEmailDialog = true">
                                                    テスト送信
                                                </v-btn>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>オンライン予約受付</td>
                                        <td>
                                            <v-chip
                                                    :color="props.shop
                                                        .accepts_online_bookings
                                                        ? 'green'
                                                        : 'red'
                                                        "
                                                    size="small">
                                                {{
                                                    props.shop
                                                        .accepts_online_bookings
                                                        ? "受付中"
                                                        : "停止中"
                                                }}
                                            </v-chip>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>予約枠の間隔</td>
                                        <td>
                                            {{ props.shop.time_slot_interval }} 分
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>キャンセル期限</td>
                                        <td>
                                            {{
                                                formatDeadline(
                                                    props.shop
                                                        .cancellation_deadline_minutes
                                                )
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>予約締切</td>
                                        <td>
                                            {{
                                                formatDeadline(
                                                    props.shop.booking_deadline_minutes
                                                )
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>タイムゾーン</td>
                                        <td>{{ props.shop.timezone }}</td>
                                    </tr>
                                </tbody>
                            </v-table>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>


        <!-- テストメール送信確認ダイアログ -->
        <v-dialog v-model="testEmailDialog" max-width="500px">
            <v-card>
                <v-card-title class="text-h5">
                    テストメール送信
                </v-card-title>
                <v-card-text>
                    現在設定されているメールアドレス ({{ props.shop.email }}) にテストメールを送信します。<br>
                    よろしいですか？
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                           color="blue-darken-1"
                           variant="text"
                           @click="testEmailDialog = false">
                        キャンセル
                    </v-btn>
                    <v-btn
                           color="blue-darken-1"
                           variant="text"
                           :loading="sendingTestEmail"
                           @click="sendTestEmail">
                        送信する
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar
                    v-model="snackbar.show"
                    :color="snackbar.color"
                    timeout="3000">
            {{ snackbar.message }}
        </v-snackbar>
    </OwnerLayout>
</template>

<script setup lang="ts">
import { useDisplay } from "vuetify";
import OwnerLayout from "@/components/owner/OwnerLayout.vue";
import { useTimeFormatter } from "@/composables/useTimeFormatter";

import axios from "axios";
import { ref } from "vue";

interface Shop {
    id: number;
    name: string;
    slug: string;
    time_slot_interval: number;
    booking_confirmation_type: string;
    accepts_online_bookings: boolean;
    timezone: string;
    cancellation_deadline_minutes: number;
    booking_deadline_minutes: number;
    email?: string;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    shop: Shop;
}>();

const { smAndDown } = useDisplay();
const { formatDeadline } = useTimeFormatter();

const testEmailDialog = ref(false);
const sendingTestEmail = ref(false);
const snackbar = ref({
    show: false,
    message: "",
    color: "success",
});

const sendTestEmail = async () => {
    sendingTestEmail.value = true;
    try {
        await axios.post(`/owner/api/shops/${props.shop.slug}/test-email`);
        snackbar.value = {
            show: true,
            message: "テストメールを送信しました。",
            color: "success",
        };
        testEmailDialog.value = false;
    } catch (error) {
        console.error(error);
        snackbar.value = {
            show: true,
            message: "テストメールの送信に失敗しました。",
            color: "error",
        };
    } finally {
        sendingTestEmail.value = false;
    }
};

</script>
