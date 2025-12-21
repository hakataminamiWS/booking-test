<template>
    <v-container>
        <!-- Navigation -->
        <v-row>
            <v-col cols="12">
                <v-btn
                       :href="`/shops/${shop.slug}/booker/bookings`"
                       prepend-icon="mdi-arrow-left"
                       variant="text">
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
            <v-col cols="12" md="8">
                <v-card class="mb-6">
                    <v-card-title class="d-flex align-center">
                        予約詳細
                        <v-spacer></v-spacer>
                        <v-chip :color="getStatusColor(booking.status)" size="small">
                            {{ getStatusText(booking.status) }}
                        </v-chip>
                    </v-card-title>
                    
                    <v-card-text>
                        <v-alert v-if="props.successMessage" type="success" class="mb-4" density="compact" variant="tonal">
                            {{ props.successMessage }}
                        </v-alert>

                        <v-alert v-if="props.errorMessage" type="error" class="mb-4" density="compact" variant="tonal">
                            {{ props.errorMessage }}
                        </v-alert>

                        <!-- Time & Staff Section -->
                        <div class="mb-6">
                            <h3 class="text-h6 font-weight-bold mb-2">予約日時・担当</h3>
                            <v-list density="compact" class="pa-0">
                                <v-list-item class="px-0">
                                    <template v-slot:prepend>
                                        <v-icon color="grey-darken-1" class="mr-3">mdi-calendar-clock</v-icon>
                                    </template>
                                    <v-list-item-title class="text-subtitle-2 text-grey">予約日時</v-list-item-title>
                                    <v-list-item-subtitle class="text-body-1 font-weight-bold text-high-emphasis">
                                        {{ formatDate(booking.start_at) }}<br />
                                        {{ formatTime(booking.start_at) }} 〜 {{ formatTime(booking.end_at) }}
                                        <span class="text-body-2 text-grey ml-1">({{ totalDuration }}分)</span>
                                    </v-list-item-subtitle>
                                </v-list-item>

                                <v-list-item class="px-0 mt-2">
                                    <template v-slot:prepend>
                                        <v-icon color="grey-darken-1" class="mr-3">mdi-account-star</v-icon>
                                    </template>
                                    <v-list-item-title class="text-subtitle-2 text-grey">担当スタッフ</v-list-item-title>
                                    <v-list-item-subtitle class="text-body-1 font-weight-bold text-high-emphasis">
                                        {{ booking.assigned_staff_name || '指名なし' }}
                                    </v-list-item-subtitle>
                                </v-list-item>
                            </v-list>
                        </div>

                        <v-divider class="mb-6"></v-divider>

                        <!-- Menu Details Section -->
                        <div class="mb-6">
                            <h3 class="text-h6 font-weight-bold mb-2">メニュー詳細</h3>
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

                                <template v-if="booking.booking_options && booking.booking_options.length > 0">
                                    <v-list-item v-for="opt in booking.booking_options" :key="opt.id" class="px-0 pt-2">
                                        <div class="d-flex justify-space-between align-center w-100">
                                            <div>
                                                <div class="text-high-emphasis">{{ opt.option_name }}</div>
                                                <div class="text-caption text-grey">+{{ opt.option_duration }}分</div>
                                            </div>
                                            <div class="text-body-1">¥{{ opt.option_price.toLocaleString() }}</div>
                                        </div>
                                    </v-list-item>
                                </template>
                            </v-list>

                            <v-divider class="my-4"></v-divider>
                            <div class="d-flex justify-space-between align-center py-2">
                                <span class="text-h6 font-weight-bold">合計金額</span>
                                <span class="text-h6 font-weight-bold text-primary">¥{{ totalPrice.toLocaleString() }}</span>
                            </div>
                        </div>

                        <v-divider class="mb-6"></v-divider>

                        <!-- Booker Info Section -->
                        <div class="mb-6">
                            <h3 class="text-h6 font-weight-bold mb-2">お客様情報</h3>
                            <v-list density="compact" class="pa-0">
                                <v-list-item class="px-0">
                                    <v-list-item-title class="text-subtitle-2 text-grey">お名前</v-list-item-title>
                                    <v-list-item-subtitle class="text-body-1 text-high-emphasis">{{ booking.booker_name }} 様</v-list-item-subtitle>
                                </v-list-item>
                                <v-list-item class="px-0 pt-2">
                                    <v-list-item-title class="text-subtitle-2 text-grey">メールアドレス</v-list-item-title>
                                    <v-list-item-subtitle class="text-body-1 text-high-emphasis">{{ booking.contact_email }}</v-list-item-subtitle>
                                </v-list-item>
                                <v-list-item class="px-0 pt-2">
                                    <v-list-item-title class="text-subtitle-2 text-grey">電話番号</v-list-item-title>
                                    <v-list-item-subtitle class="text-body-1 text-high-emphasis">{{ booking.contact_phone }}</v-list-item-subtitle>
                                </v-list-item>
                            </v-list>
                        </div>

                        <!-- Notes Section -->
                        <div v-if="booking.note_from_booker" class="mb-6">
                            <h3 class="text-h6 font-weight-bold mb-2">備考</h3>
                            <v-sheet border rounded class="pa-3 bg-grey-lighten-4">
                                <p class="text-body-2" style="white-space: pre-wrap;">{{ booking.note_from_booker }}</p>
                            </v-sheet>
                        </div>
                    </v-card-text>

                    <v-divider></v-divider>

                    <v-card-actions v-if="booking.status !== 'cancelled' && canCancel" class="pa-4">
                        <v-spacer></v-spacer>
                        <v-btn
                               color="error"
                               variant="flat"
                               @click="showCancelDialog = true">
                            予約をキャンセルする
                        </v-btn>
                    </v-card-actions>
                    <v-card-text v-else-if="booking.status !== 'cancelled'" class="pa-4">
                        <v-alert type="warning" density="compact" variant="tonal" border="start">
                            キャンセル期限を過ぎているため、オンラインからのキャンセルはできません。変更・キャンセルについては、店舗へ直接お電話でご連絡ください。
                        </v-alert>
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
    getAuthenticatedBooker: () => void; // Unused but kept for structure if needed
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
