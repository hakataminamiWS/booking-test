<template>
  <v-container v-if="parsedBookingDetails">
    <v-card class="mx-auto" max-width="600">
      <v-card-title class="text-h5 font-weight-bold">予約詳細 (ID: {{ bookingId }})</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-list lines="one">
          <v-list-item title="日付" :subtitle="parsedBookingDetails.date" prepend-icon="mdi-calendar"></v-list-item>
          <v-list-item title="時間" :subtitle="parsedBookingDetails.time" prepend-icon="mdi-clock-outline"></v-list-item>
          <v-list-item title="担当者" :subtitle="parsedBookingDetails.staff_name" prepend-icon="mdi-account-outline"></v-list-item>
          <v-list-item title="サービス" :subtitle="parsedBookingDetails.service_name" prepend-icon="mdi-auto-fix"></v-list-item>
          <v-list-item title="ステータス" :subtitle="parsedBookingDetails.status" prepend-icon="mdi-information-outline"></v-list-item>
          <v-list-item title="備考" :subtitle="parsedBookingDetails.notes" prepend-icon="mdi-note-text-outline"></v-list-item>
        </v-list>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-btn :href="myBookingsUrl">一覧に戻る</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="secondary" :href="bookingEditUrl">変更</v-btn>
        <v-btn color="error" @click="confirmCancel">キャンセル</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
  <v-container v-else>
    <p class="text-center">予約情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface BookingDetails {
  id: number;
  date: string;
  time: string;
  staff_name: string;
  service_name: string;
  status: string;
  notes: string;
}

const props = defineProps<{
  shopId: string;
  bookingId: string;
  bookingDetails: string; // JSON文字列として受け取る
}>();

const parsedBookingDetails = ref<BookingDetails | null>(null);

const myBookingsUrl = computed(() => `/shops/${props.shopId}/bookings`);
const bookingEditUrl = computed(() => `/shops/${props.shopId}/bookings/${props.bookingId}/edit`);

const confirmCancel = () => {
  if (confirm(`予約ID: ${props.bookingId} をキャンセルしますか？`)) {
    console.log(`Cancelling booking ${props.bookingId}`);
    alert(`予約ID: ${props.bookingId} をキャンセルしました。（ダミー）`);
  }
};

onMounted(() => {
  try {
    parsedBookingDetails.value = JSON.parse(props.bookingDetails);
  } catch (e) {
    console.error("Failed to parse booking details:", e);
    parsedBookingDetails.value = null;
  }
});
</script>
