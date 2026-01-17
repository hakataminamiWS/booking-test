<template>
    <StaffLayout :shop="shop" :current-page="'dashboard'">
        <v-container>
            <!-- Status Sections -->
            <v-row class="mb-4">
                <!-- Today's Cancellations -->
                <v-col cols="12" md="6">
                    <v-card variant="text">
                        <v-card-title>
                            本日のキャンセル
                        </v-card-title>
                        <v-card-subtitle>
                            {{ currentDate }}
                        </v-card-subtitle>

                        <v-card-text>
                            <div v-if="cancellations.count > 0" class="d-flex align-center justify-center">
                                <span class="text-h6 text-error">{{ cancellations.count }}件</span>
                                <v-btn :href="cancellations.actionUrl || '/shops/' + shop.slug + '/staff/bookings?status=cancelled'"
                                       color="error"
                                       variant="text"
                                       append-icon="mdi-arrow-right">
                                    確認する
                                </v-btn>
                            </div>
                            <div v-else class="text-grey py-4">
                                キャンセルはありません
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <!-- Shift Status -->
                <v-col cols="12" md="6">
                    <v-card variant="text">
                        <v-card-title>
                            シフト登録状況
                        </v-card-title>
                        <v-card-subtitle>
                            {{ shifts.range_start }} 〜 {{ shifts.range_end }}
                        </v-card-subtitle>
                        <v-card-text>
                            <div v-if="shifts.status === 'incomplete'">
                                <div class="text-warning font-weight-bold">
                                    以下のスタッフのシフトが未登録です
                                </div>
                                <v-chip-group>
                                    <!-- 自身のシフトのみクリック可能 -->
                                    <v-chip v-for="staff in shifts.unregistered_staff" :key="staff.id" color="warning"
                                            variant="outlined"
                                            :link="isCurrentStaff(staff.id)"
                                            :href="isCurrentStaff(staff.id) ? `/shops/${shop.slug}/staff/shifts/edit` : undefined">
                                        {{ staff.name }}
                                        <v-icon v-if="isCurrentStaff(staff.id)" end
                                                size="small">mdi-arrow-right</v-icon>
                                    </v-chip>
                                </v-chip-group>
                            </div>
                            <div v-else class="d-flex align-center justify-center">
                                全スタッフ登録済み
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Today's Bookings Section -->
            <v-card variant="text">
                <v-card-title>
                    本日の予約（{{ summary.total_today }}件）
                </v-card-title>
                <v-card-subtitle>
                    {{ currentDate }}
                </v-card-subtitle>

                <v-card-text>
                    <template v-if="bookings.length === 0">
                        <p class="text-grey">本日の予約はありません</p>
                    </template>

                    <v-timeline v-else align="start" side="end">
                        <v-timeline-item v-for="booking in bookings" :key="booking.id" :dot-color="getDotColor(booking)"
                                         size="large"
                                         fill-dot>
                            <template v-slot:opposite>
                                <div class="text-right">
                                    <div class="font-weight-bold text-body-2">{{ booking.staff_name }}</div>
                                </div>
                            </template>
                            <template v-slot:icon>
                                <v-avatar v-if="booking.staff_image_url" :image="booking.staff_image_url"></v-avatar>
                                <v-avatar v-else color="grey-lighten-2">
                                    <v-icon icon="mdi-account"></v-icon>
                                </v-avatar>
                            </template>

                            <v-card :class="['mb-4', { 'next-arrival-card': isNextArrival(booking.id) }]"
                                    :elevation="isNextArrival(booking.id) ? 6 : 1"
                                    :variant="booking.is_past ? 'tonal' : 'elevated'">
                                <v-card-title class="d-flex justify-space-between align-center">
                                    <div class="text-h6 font-weight-bold primary--text">
                                        {{ booking.start_at }} - {{ booking.end_at }}
                                        <span v-if="isNextArrival(booking.id)" class="ml-2">
                                            <v-chip color="primary" size="small" class="font-weight-bold">次の来店</v-chip>
                                        </span>
                                    </div>
                                </v-card-title>

                                <v-card-text>
                                    <div class="d-flex flex-wrap align-center">
                                        <div class="flex-grow-1 mr-4">
                                            <div class="text-subtitle-1 font-weight-bold">{{ booking.customer_name }} 様
                                            </div>
                                            <div class="text-body-2 text-grey-darken-2 mb-0">
                                                {{ booking.menu_name }}
                                            </div>
                                        </div>
                                        <div class="d-flex align-center justify-end flex-grow-1 flex-md-grow-0 mt-2 mt-md-0"
                                             v-if="!booking.is_past">
                                            <!-- スタッフ用予約編集画面への遷移 -->
                                            <v-btn :href="`/shops/${shop.slug}/staff/bookings/${booking.id}/edit`"
                                                   variant="text"
                                                   color="primary" append-icon="mdi-arrow-right">
                                                詳細
                                            </v-btn>
                                        </div>
                                    </div>
                                    <!-- 予約時メモ -->
                                    <div v-if="booking.note_from_booker" class="mt-2 text-body-2 text-grey-darken-1">
                                        <v-icon size="small" class="mr-1">mdi-note-text-outline</v-icon>
                                        {{ booking.note_from_booker }}
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-timeline-item>
                    </v-timeline>
                </v-card-text>
            </v-card>
        </v-container>
    </StaffLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useDisplay } from "vuetify";
import { useBookingStatus } from "@/composables/useBookingStatus";

const { getStatusText, getStatusColor } = useBookingStatus();
import StaffLayout from '@/components/staff/StaffLayout.vue';

interface Shop {
    name: string;
    slug: string;
}

interface Cancellations {
    count: number;
    actionUrl: string | null;
}

interface Shifts {
    status: 'complete' | 'incomplete';
    unregistered_staff: { id: number; name: string }[];
    range_start: string;
    range_end: string;
}

interface Booking {
    id: number;
    start_at: string; // HH:mm
    end_at: string;
    customer_name: string;
    menu_name: string;
    staff_name: string;
    staff_image_url: string | null;
    status: string;
    is_past: boolean;
    note_from_booker: string | null;
}

interface Summary {
    total_today: number;
}

const props = defineProps<{
    shop: Shop;
    bookings: Booking[];
    cancellations: Cancellations;
    shifts: Shifts;
    summary: Summary;
    nextArrivalId: number | null;
    currentUserStaffId: number | null;
}>();

// --- Computed ---
const currentDate = computed(() => {
    return new Date().toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
    });
});

// --- Helpers ---
/**
 * 指定されたスタッフIDがログイン中のスタッフ自身かどうかを判定
 */
const isCurrentStaff = (staffId: number) => {
    return props.currentUserStaffId === staffId;
};

const isNextArrival = (id: number) => {
    return props.nextArrivalId === id;
};

const getDotColor = (booking: Booking) => {
    if (isNextArrival(booking.id)) return 'primary';
    if (booking.is_past) return 'grey';
    return 'secondary';
};



</script>

<style scoped>
.next-arrival-card {
    border-left: 4px solid rgb(var(--v-theme-primary));
}
</style>
