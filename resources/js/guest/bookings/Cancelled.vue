<template>
    <BookerLayout :shop="shop">
        <v-container class="container-width-600 px-4">
            <v-row justify="center">
                <v-col cols="12">
                    <v-card class="mb-6" variant="text">
                        <v-card-text class="text-center py-6">
                            <v-icon size="64" color="grey" class="mb-4">mdi-cancel</v-icon>
                            <h2 class="text-h5 font-weight-bold mb-2">予約をキャンセルしました</h2>
                            <p class="text-body-1 text-grey-darken-1">
                                ご予約のキャンセルを承りました。<br>
                                またのご利用をお待ちしております。
                            </p>
                        </v-card-text>

                        <v-divider></v-divider>

                        <v-card-title class="px-4 py-3">
                            キャンセル内容
                        </v-card-title>

                        <v-card-text class="px-4 pb-4">
                            <v-table density="compact">
                                <tbody>
                                    <tr>
                                        <td class="text-grey">日時</td>
                                        <td>
                                            {{ formatDate(booking.start_at) }}
                                            {{ formatTime(booking.start_at) }} 〜
                                            {{ formatTime(booking.end_at) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-grey">メニュー</td>
                                        <td>{{ booking.menu_name }}</td>
                                    </tr>
                                    <tr v-if="booking.assigned_staff_name">
                                        <td class="text-grey">担当</td>
                                        <td>{{ booking.assigned_staff_name }}</td>
                                    </tr>
                                </tbody>
                            </v-table>
                        </v-card-text>
                    </v-card>

                    <div class="text-center">
                        <v-btn
                               color="primary"
                               :href="`/shops/${shop.slug}`">
                            店舗トップへ戻る
                        </v-btn>
                    </div>
                </v-col>
            </v-row>
        </v-container>
    </BookerLayout>
</template>

<script setup lang="ts">
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
    assigned_staff_name?: string;
}

interface Props {
    shop: Shop;
    booking: Booking;
}

const props = defineProps<Props>();

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
