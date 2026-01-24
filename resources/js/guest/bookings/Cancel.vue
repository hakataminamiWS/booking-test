<template>
    <BookerLayout :shop="booking.shop">
        <v-container class="container-width-600 px-4">
            <v-row justify="center">
                <v-col cols="12">
                    <template v-if="booking.status === 'cancelled'">
                        <v-card class="mb-6" variant="text">
                            <v-card-text class="text-center py-6">
                                <v-icon size="64" color="grey" class="mb-4">mdi-cancel</v-icon>
                                <h2 class="text-h5 font-weight-bold mb-2">予約キャンセル済み</h2>
                                <p class="text-body-1 text-grey-darken-1">この予約は既にキャンセルされています。</p>
                            </v-card-text>
                        </v-card>
                    </template>
                    <template v-else>
                        <v-card class="mb-6" variant="text">
                            <v-card-text class="text-center py-6">
                                <v-icon size="64" color="warning" class="mb-4">mdi-alert-circle-outline</v-icon>
                                <h2 class="text-h5 font-weight-bold mb-2">予約キャンセルの確認</h2>
                                <p class="text-body-1 text-grey-darken-1">以下の予約をキャンセルしますか？<br>この操作は取り消せません。</p>
                            </v-card-text>

                            <v-divider></v-divider>

                            <v-card-title class="px-4 py-3">
                                予約詳細
                            </v-card-title>

                            <v-card-text class="px-4 pb-4">
                                <div class="mb-6">
                                    <h3 class="text-subtitle-1 font-weight-bold mb-2">予約日時・担当</h3>
                                    <v-card variant="text" class="px-0 mb-4">
                                        <v-card-title
                                                      class="pa-0 text-subtitle-2 text-grey-darken-1">予約日時</v-card-title>
                                        <v-card-text class="pa-0 text-body-1 font-weight-bold text-high-emphasis">
                                            {{ formatDate(booking.start_at) }}<br />
                                            {{ formatTime(booking.start_at) }} 〜 {{ formatTime(booking.end_at) }}
                                        </v-card-text>
                                    </v-card>

                                    <v-card variant="text" class="px-0 mb-4">
                                        <v-card-title
                                                      class="pa-0 text-subtitle-2 text-grey-darken-1">メニュー</v-card-title>
                                        <v-card-text class="pa-0 text-body-1 font-weight-bold text-high-emphasis">
                                            {{ booking.menu_name }}
                                        </v-card-text>
                                    </v-card>
                                </div>
                            </v-card-text>

                            <v-card-actions class="px-4 pb-6 d-flex flex-column gap-3">
                                <v-form @submit.prevent="submitCancel" class="w-100">
                                    <v-btn
                                           type="submit"
                                           color="error"
                                           block
                                           height="48"
                                           class="font-weight-bold"
                                           :loading="loading">
                                        予約をキャンセルする
                                    </v-btn>
                                </v-form>

                                <v-btn
                                       variant="text"
                                       block
                                       class="mt-2"
                                       @click="goTop">
                                    キャンセルせずに戻る
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </template>
                </v-col>
            </v-row>
        </v-container>
    </BookerLayout>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import BookerLayout from "@/components/booker/BookerLayout.vue";

interface Shop {
    name: string;
    slug: string;
}

interface Booking {
    id: number;
    start_at: string;
    end_at: string;
    menu_name: string;
    status: string;
    shop: Shop;
}

interface Props {
    booking: Booking;
    token: string;
}

const props = defineProps<Props>();
const loading = ref(false);

const formatDate = (dateStr: string) => {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric', weekday: 'short' });
};

const formatTime = (dateStr: string) => {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleTimeString('ja-JP', { hour: '2-digit', minute: '2-digit' });
};

const submitCancel = () => {
    if (!confirm('本当にキャンセルしてよろしいですか？')) return;
    loading.value = true;
    // Helper form submission to include CSRF automatically if meta tag is present (standard Laravel blade layout has it)
    // Or create a form element and submit it.
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/bookings/cancel/${props.token}`;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_token';
        input.value = csrfToken;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
};

const goTop = () => {
    // Redirect to shop top? Accessing shop entry from booking shop slug
    window.location.href = `/shops/${props.booking.shop.slug}`;
};
</script>

<style scoped>
.container-width-600 {
    max-width: 600px;
}

.gap-3 {
    gap: 12px;
}
</style>
