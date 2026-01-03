<template>
    <v-app>
        <v-main>
            <v-container class="container-width-600 px-4">
                <!-- Navigation -->
                <v-row no-gutters>
                    <v-col cols="12">
                        <v-btn :href="`/shops/${shop.slug}/booker/bookings`" prepend-icon="mdi-arrow-left"
                               variant="text" class="px-0">
                            予約履歴に戻る
                        </v-btn>
                    </v-col>
                </v-row>

                <!-- Shop Header -->
                <v-row>
                    <v-col cols="12">
                        <ShopHeader :shop="shop" />
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="12">
                        <v-card>
                            <v-card-title class="py-4 font-weight-bold">
                                予約詳細
                            </v-card-title>

                            <v-card-text class="pt-4">
                                <v-alert v-if="props.successMessage" type="success" class="mb-4" density="compact"
                                         variant="tonal">
                                    {{ props.successMessage }}
                                </v-alert>

                                <v-alert v-if="props.errorMessage" type="error" class="mb-4" density="compact"
                                         variant="tonal">
                                    {{ props.errorMessage }}
                                </v-alert>

                                <!-- Booking Status -->
                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-text class="pa-0 text-subtitle-2 text-grey-darken-1">予約状況</v-card-text>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ getStatusText(booking.status) }}
                                    </v-card-text>
                                </v-card>

                                <!-- Time & Staff Section -->
                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-text class="pa-0 text-subtitle-2 text-grey-darken-1">予約日時</v-card-text>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ formatDateTimeSimple(booking.start_at, booking.end_at) }} ({{ totalDuration
                                        }}分)
                                    </v-card-text>
                                </v-card>

                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-text class="pa-0 text-subtitle-2 text-grey-darken-1">担当スタッフ</v-card-text>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ booking.assigned_staff_name || '指名なし' }}
                                    </v-card-text>
                                </v-card>

                                <!-- Menu Details Section -->
                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">メニュー</v-card-title>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ booking.menu_name }}
                                    </v-card-text>
                                </v-card>

                                <template v-if="booking.booking_options && booking.booking_options.length > 0">
                                    <v-card variant="text" class="px-0 mb-4">
                                        <v-card-title
                                                      class="pa-0 text-subtitle-2 text-grey-darken-1">オプション</v-card-title>
                                        <div v-for="opt in booking.booking_options" :key="opt.id" class="mt-1">
                                            <v-card-text class="pa-0 text-body-1">
                                                {{ opt.option_name }}
                                                <div class="d-flex justify-space-between text-body-2 text-grey mt-1">
                                                    <span>+{{ opt.option_duration }}分</span>
                                                    <span>+¥{{ opt.option_price.toLocaleString() }}</span>
                                                </div>
                                            </v-card-text>
                                        </div>
                                    </v-card>
                                </template>

                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-title
                                                  class="pa-0 text-subtitle-2 text-grey-darken-1">合計金額（税込）</v-card-title>
                                    <v-card-text class="pa-0 text-body-1 font-weight-bold">
                                        ¥{{ totalPrice.toLocaleString() }}
                                    </v-card-text>
                                </v-card>

                                <!-- Booker Info Section -->
                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-text class="pa-0 text-subtitle-2 text-grey-darken-1">お名前</v-card-text>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ booking.booker_name }} 様
                                    </v-card-text>
                                </v-card>
                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-text class="pa-0 text-subtitle-2 text-grey-darken-1">メールアドレス</v-card-text>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ booking.contact_email }}
                                    </v-card-text>
                                </v-card>
                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-text class="pa-0 text-subtitle-2 text-grey-darken-1">電話番号</v-card-text>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ booking.contact_phone }}
                                    </v-card-text>
                                </v-card>

                                <!-- Notes Section -->
                                <v-card v-if="booking.note_from_booker" variant="text" class="px-0 mb-4">
                                    <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">備考</v-card-title>
                                    <v-card-text class="pa-0 text-body-1">
                                        <v-sheet border rounded class="pa-3 bg-grey-lighten-4">
                                            <p class="text-body-2" style="white-space: pre-wrap;">{{
                                                booking.note_from_booker
                                                }}
                                            </p>
                                        </v-sheet>
                                    </v-card-text>
                                </v-card>

                                <!-- Actions -->
                                <div v-if="booking.status !== 'cancelled' && canCancel" class="mt-6">
                                    <v-btn block color="error" variant="flat" @click="showCancelDialog = true">
                                        予約をキャンセルする
                                    </v-btn>
                                </div>
                                <div v-else-if="booking.status !== 'cancelled'" class="mt-6">
                                    <v-alert type="warning" density="compact" variant="tonal" border="start">
                                        キャンセル期限を過ぎているため、オンラインからのキャンセルはできません。変更・キャンセルについては、店舗へ直接お電話でご連絡ください。
                                    </v-alert>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>

                <!-- Cancel Confirmation Dialog -->
                <v-dialog v-model="showCancelDialog" max-width="400px">
                    <v-card>
                        <v-card-title class="text-h6">予約キャンセル</v-card-title>
                        <v-card-text>
                            この予約をキャンセルしてもよろしいですか？<br />
                            一度キャンセルすると元に戻すことはできません。
                        </v-card-text>
                        <v-card-actions class="pa-4">
                            <v-spacer></v-spacer>
                            <v-btn variant="text" @click="showCancelDialog = false">いいえ</v-btn>
                            <form id="cancelForm" :action="cancelUrl" method="POST" style="display: inline;">
                                <input type="hidden" name="_token" :value="props.csrfToken" />
                                <input type="hidden" name="_method" value="DELETE" />
                                <v-btn color="error" variant="flat" type="submit" form="cancelForm">はい、キャンセルする</v-btn>
                            </form>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </v-container>
        </v-main>
    </v-app>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import ShopHeader from "@/components/common/ShopHeader.vue";

interface Shop {
    name: string;
    slug: string;
}

interface BookingOption {
    id: number;
    option_name: string;
    option_price: number;
    option_duration: number;
}

interface AssignedStaff {
    profile?: {
        nickname: string;
    };
}

interface Booking {
    id: number;
    start_at: string;
    end_at: string;
    menu_name: string;
    menu_price: number;
    menu_duration: number;
    assigned_staff_name: string | null;
    status: string;
    booker_name: string;
    contact_email: string;
    contact_phone: string;
    note_from_booker: string | null;
    booking_options?: BookingOption[];
}

const props = defineProps<{
    shop: Shop;
    booker: { id: number; name: string };
    booking: Booking;
    csrfToken: string;
    successMessage: string | null;
    errorMessage: string | null;
    cancellationDeadlineMinutes: number;
}>();

const showCancelDialog = ref(false);

const cancelUrl = computed(() => `/shops/${props.shop.slug}/booker/bookings/${props.booking.id}`);

// Total calculation
const totalPrice = computed(() => {
    let total = props.booking.menu_price;
    props.booking.booking_options?.forEach(opt => {
        total += opt.option_price;
    });
    return total;
});

const totalDuration = computed(() => {
    let total = props.booking.menu_duration;
    props.booking.booking_options?.forEach(opt => {
        total += opt.option_duration;
    });
    return total;
});

// Check if cancellation is allowed
const canCancel = computed(() => {
    // Ensure the date is parsed as UTC by appending 'Z' if not present
    const startAtStr = props.booking.start_at.endsWith('Z') ? props.booking.start_at : props.booking.start_at + 'Z';
    const startAt = new Date(startAtStr);

    // Use the dynamic deadline passed from backend
    const deadline = new Date(startAt.getTime() - props.cancellationDeadlineMinutes * 60 * 1000);
    return new Date() < deadline;
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

const formatDateTimeSimple = (start: string, end: string) => {
    if (!start || !end) return "";
    const startDate = new Date(start);
    const endDate = new Date(end);

    const year = startDate.getFullYear();
    const month = String(startDate.getMonth() + 1).padStart(2, '0');
    const day = String(startDate.getDate()).padStart(2, '0');
    const startTime = startDate.toLocaleTimeString('ja-JP', { hour: '2-digit', minute: '2-digit' });
    const endTime = endDate.toLocaleTimeString('ja-JP', { hour: '2-digit', minute: '2-digit' });

    return `${year}-${month}-${day} ${startTime}〜${endTime}`;
};

const formatDateTime = (dateStr: string) => {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    return date.toLocaleString('ja-JP', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'confirmed': return '予約確定';
        case 'pending': return '保留中';
        case 'cancelled': return 'キャンセル済み';
        default: return status;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'confirmed': return 'success';
        case 'pending': return 'warning';
        case 'cancelled': return 'error';
        default: return 'grey';
    }
};
</script>

<style scoped>
.container-width-600 {
    max-width: 600px;
}
</style>
