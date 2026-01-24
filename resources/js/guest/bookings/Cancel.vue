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
                                <!-- Time & Staff Section -->
                                <div class="mb-6">
                                    <h3 class="text-subtitle-1 font-weight-bold mb-2">予約日時・担当</h3>
                                    <v-card variant="text" class="px-0 mb-4">
                                        <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">予約日時</v-card-title>
                                        <v-card-text class="pa-0 text-body-1 font-weight-bold text-high-emphasis">
                                            {{ formatDate(booking.start_at) }}<br />
                                            {{ formatTime(booking.start_at) }} 〜 {{ formatTime(booking.end_at) }}
                                            <span class="text-body-2 text-grey ml-1">({{ totalDuration }}分)</span>
                                        </v-card-text>
                                    </v-card>

                                    <v-card variant="text" class="px-0 mb-4">
                                        <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">担当スタッフ</v-card-title>
                                        <v-card-text class="pa-0 text-body-1 font-weight-bold text-high-emphasis">
                                            {{ booking.assigned_staff_name || '指名なし' }}
                                        </v-card-text>
                                    </v-card>
                                </div>

                                <v-divider class="mb-6"></v-divider>

                                <!-- Menu Details Section -->
                                <div class="mb-6">
                                    <h3 class="text-subtitle-1 font-weight-bold mb-2">メニュー詳細</h3>
                                    <v-list density="compact" class="pa-0">
                                        <v-list-item class="px-0">
                                            <div class="d-flex justify-space-between align-center w-100">
                                                <div>
                                                    <div class="text-high-emphasis font-weight-medium">{{ booking.menu_name }}</div>
                                                    <div class="text-caption text-grey">{{ booking.menu_duration }}分</div>
                                                </div>
                                                <div class="text-body-1 font-weight-bold">¥{{ booking.menu_price.toLocaleString() }}</div>
                                            </div>
                                        </v-list-item>

                                        <template v-if="booking.bookingOptions && booking.bookingOptions.length > 0">
                                            <v-list-item v-for="opt in booking.bookingOptions" :key="opt.option_name" class="px-0 pt-2">
                                                <div class="d-flex justify-space-between align-center w-100">
                                                    <div>
                                                        <div class="text-high-emphasis">{{ opt.option_name }}</div>
                                                        <div class="text-caption text-grey">+{{ opt.option_duration ?? 0 }}分</div>
                                                    </div>
                                                    <div class="text-body-1">¥{{ opt.option_price?.toLocaleString() ?? 0 }}</div>
                                                </div>
                                            </v-list-item>
                                        </template>
                                    </v-list>

                                    <v-divider class="my-4"></v-divider>
                                    <div class="d-flex justify-space-between align-center py-2">
                                        <span class="text-subtitle-1 font-weight-bold">合計金額</span>
                                        <span class="text-h6 font-weight-bold text-primary">¥{{ totalPrice.toLocaleString() }}</span>
                                    </div>
                                </div>

                                <!-- Note Section -->
                                <template v-if="booking.note_from_booker">
                                    <v-divider class="mb-6"></v-divider>
                                    <div class="mb-6">
                                        <h3 class="text-subtitle-1 font-weight-bold mb-2">お客様からのメモ</h3>
                                        <p class="text-body-1 text-grey-darken-1" style="white-space: pre-wrap;">{{ booking.note_from_booker }}</p>
                                    </div>
                                </template>
                            </v-card-text>

                            <v-card-actions class="px-4 pb-6 d-flex flex-column flex-sm-row-reverse gap-3 justify-center">
                                <v-form @submit.prevent="submitCancel" class="w-100 w-sm-auto">
                                    <v-btn
                                        type="submit"
                                        color="error"
                                        block
                                        height="48"
                                        class="font-weight-bold"
                                        :loading="loading"
                                        min-width="200">
                                        予約をキャンセルする
                                    </v-btn>
                                </v-form>

                                <v-btn
                                    variant="text"
                                    block
                                    class="w-100 w-sm-auto mt-0"
                                    height="48"
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

interface BookingOption {
    option_name: string;
    option_price?: number;
    option_duration?: number;
}

interface Booking {
    id: number;
    start_at: string;
    end_at: string;
    menu_name: string;
    menu_price: number;
    menu_duration: number;
    assigned_staff_name: string;
    status: string;
    note_from_booker?: string;
    bookingOptions: BookingOption[];
    shop: Shop;
}

interface Props {
    booking: Booking;
    cancelUrl: string;
}

const props = defineProps<Props>();
const loading = ref(false);

const totalPrice = computed(() => {
    let total = props.booking.menu_price;
    props.booking.bookingOptions?.forEach(opt => {
        total += opt.option_price ?? 0;
    });
    return total;
});

const totalDuration = computed(() => {
    let total = props.booking.menu_duration;
    props.booking.bookingOptions?.forEach(opt => {
        total += opt.option_duration ?? 0;
    });
    return total;
});

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
    
    // Create form for submission (Standard POST with CSRF)
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = props.cancelUrl;

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
