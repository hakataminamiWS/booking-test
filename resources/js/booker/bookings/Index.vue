<template>
  <v-container>
    <v-card class="mx-auto" max-width="800">
      <v-card-title class="text-h5 font-weight-bold">マイ予約一覧 (店舗ID: {{ shopId }})</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="parsedBookings"
          item-value="id"
          class="elevation-1"
          no-data-text="現在、予約はありません。"
        >
          <template v-slot:item.actions="{ item }">
            <v-btn
              color="primary"
              variant="text"
              size="small"
              :href="bookingDetailUrl(item.id)"
            >
              詳細
            </v-btn>
            <v-btn
              color="secondary"
              variant="text"
              size="small"
              :href="bookingEditUrl(item.id)"
            >
              変更
            </v-btn>
            <v-btn
              color="error"
              variant="text"
              size="small"
              @click="confirmCancel(item.id)"
            >
              キャンセル
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
      <v-card-actions>
        <v-btn color="success" :href="newBookingUrl">新規予約</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface Booking {
  id: number;
  booking_date: string;
  booking_time: string;
  status: string;
  // 他のプロパティも必要に応じて追加
}

const props = defineProps<{
  shopId: string;
  bookings: string; // JSON文字列として受け取る
}>();

const parsedBookings = ref<Booking[]>([]);

const headers = [
  { title: '予約ID', key: 'id' },
  { title: '予約日', key: 'booking_date' },
  { title: '予約時間', key: 'booking_time' },
  { title: 'ステータス', key: 'status' },
  { title: '操作', key: 'actions', sortable: false },
];

const bookingDetailUrl = (bookingId: number) => `/shops/${props.shopId}/bookings/${bookingId}`;
const bookingEditUrl = (bookingId: number) => `/shops/${props.shopId}/bookings/${bookingId}/edit`;
const newBookingUrl = computed(() => `/shops/${props.shopId}/bookings/new`);

const confirmCancel = (bookingId: number) => {
  if (confirm(`予約ID: ${bookingId} をキャンセルしますか？`)) {
    // ここにキャンセル処理（APIコールなど）を実装
    console.log(`Cancelling booking ${bookingId}`);
    alert(`予約ID: ${bookingId} をキャンセルしました。（ダミー）`);
  }
};

onMounted(() => {
  try {
    parsedBookings.value = JSON.parse(props.bookings);
  } catch (e) {
    console.error("Failed to parse bookings data:", e);
    parsedBookings.value = [];
  }
});
</script>
