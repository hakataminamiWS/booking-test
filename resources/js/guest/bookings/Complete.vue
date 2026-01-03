<template>
    <v-app>
        <v-main>
            <v-container class="container-width-600 px-4">
                <!-- Shop Header -->
                <v-row class="mt-4">
                    <v-col cols="12">
                        <ShopHeader :shop="shop" />
                    </v-col>
                </v-row>

                <v-row justify="center">
                    <v-col cols="12">
                        <v-card class="mb-6" variant="text">
                            <v-card-text class="text-center py-6">
                                <v-icon size="64" color="success" class="mb-4">mdi-check-circle-outline</v-icon>
                                <h2 class="text-h5 font-weight-bold mb-2">予約を受け付けました</h2>
                                <p class="text-body-1 text-grey-darken-1">完了メールをお送りしましたのでご確認ください。</p>
                            </v-card-text>

                            <v-divider></v-divider>

                            <v-card-title class="px-4 py-3">
                                予約詳細
                            </v-card-title>

                            <v-card-text class="px-4 pb-4">
                                <!-- Time & Staff Section -->
                                <div class="mb-6">
                                    <h3 class="text-subtitle-1 font-weight-bold mb-2">予約日時・担当</h3>
                                    <v-list density="compact" class="pa-0">
                                        <v-list-item class="px-0">
                                            <v-list-item-title class="text-caption text-grey">予約日時</v-list-item-title>
                                            <v-list-item-subtitle
                                                                  class="text-body-1 font-weight-bold text-high-emphasis">
                                                {{ formatDate(booking.start_at) }}<br />
                                                {{ formatTime(booking.start_at) }} 〜 {{ formatTime(booking.end_at) }}
                                                <span class="text-body-2 text-grey ml-1">({{ totalDuration }}分)</span>
                                            </v-list-item-subtitle>
                                        </v-list-item>

                                        <v-list-item class="px-0 mt-2">
                                            <v-list-item-title class="text-caption text-grey">担当スタッフ</v-list-item-title>
                                            <v-list-item-subtitle
                                                                  class="text-body-1 font-weight-bold text-high-emphasis">
                                                {{ booking.assigned_staff_name || '指名なし' }}
                                            </v-list-item-subtitle>
                                        </v-list-item>
                                    </v-list>
                                </div>

                                <v-divider class="mb-6"></v-divider>

                                <!-- Menu Details Section -->
                                <div class="mb-6">
                                    <h3 class="text-subtitle-1 font-weight-bold mb-2">メニュー詳細</h3>
                                    <v-list density="compact" class="pa-0">
                                        <v-list-item class="px-0">
                                            <div class="d-flex justify-space-between align-center w-100">
                                                <div>
                                                    <div class="text-high-emphasis font-weight-medium">{{
                                                        booking.menu_name }}</div>
                                                    <div class="text-caption text-grey">{{ booking.menu_duration }}分
                                                    </div>
                                                </div>
                                                <div class="text-body-1 font-weight-bold">¥{{
                                                    booking.menu_price.toLocaleString() }}</div>
                                            </div>
                                        </v-list-item>

                                        <template v-if="booking.bookingOptions && booking.bookingOptions.length > 0">
                                            <v-list-item v-for="opt in booking.bookingOptions" :key="opt.option_name"
                                                         class="px-0 pt-2">
                                                <div class="d-flex justify-space-between align-center w-100">
                                                    <div>
                                                        <div class="text-high-emphasis">{{ opt.option_name }}</div>
                                                        <div class="text-caption text-grey">+{{ opt.option_duration ?? 0
                                                        }}分</div>
                                                    </div>
                                                    <div class="text-body-1">¥{{ opt.option_price?.toLocaleString() ?? 0
                                                    }}</div>
                                                </div>
                                            </v-list-item>
                                        </template>
                                    </v-list>

                                    <v-divider class="my-4"></v-divider>
                                    <div class="d-flex justify-space-between align-center py-2">
                                        <span class="text-subtitle-1 font-weight-bold">合計金額</span>
                                        <span class="text-h6 font-weight-bold text-primary">¥{{
                                            totalPrice.toLocaleString() }}</span>
                                    </div>
                                </div>
                            </v-card-text>
                        </v-card>

                        <div class="text-center">
                            <v-btn color="primary" variant="outlined"
                                   :href="`/shops/${shop.slug}/guest/bookings/create`" block class="py-6">
                                新しい予約をする
                            </v-btn>
                        </div>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<script setup lang="ts">
import { computed } from "vue";
import ShopHeader from "@/components/common/ShopHeader.vue";

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
    bookingOptions: BookingOption[];
}

interface Props {
    shop: Shop;
    booking: Booking;
}

const props = defineProps<Props>();

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
</script>

<style scoped>
.container-width-600 {
    max-width: 600px;
}
</style>
