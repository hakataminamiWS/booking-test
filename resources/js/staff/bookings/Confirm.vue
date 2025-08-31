<template>
  <v-container v-if="parsedBookingDetails">
    <v-card class="mx-auto" max-width="600">
      <v-card-title class="text-h5 font-weight-bold">予約内容の確認</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <p class="text-center mb-6">以下の内容で予約を作成します。</p>
        
        <v-list lines="one">
          <v-list-item title="日付" :subtitle="parsedBookingDetails.date" prepend-icon="mdi-calendar"></v-list-item>
          <v-list-item title="時間" :subtitle="parsedBookingDetails.time" prepend-icon="mdi-clock-outline"></v-list-item>
          <v-list-item title="担当者" :subtitle="parsedBookingDetails.staff_name" prepend-icon="mdi-account-outline"></v-list-item>
          <v-list-item title="サービス" :subtitle="parsedBookingDetails.service_name" prepend-icon="mdi-auto-fix"></v-list-item>
          <v-list-item title="予約者名" :subtitle="parsedBookingDetails.booker_name"></v-list-item>
          <v-list-item title="メールアドレス" :subtitle="parsedBookingDetails.booker_email"></v-list-item>
          <v-list-item title="電話番号" :subtitle="parsedBookingDetails.booker_tel"></v-list-item>
        </v-list>

        <v-divider class="my-4"></v-divider>
        <p class="text-subtitle-1 font-weight-bold">検討事項:</p>
        <p>予約確定後、予約者へメールを送信するかしないかを選択できるようにする？</p>
        <p class="text-caption text-grey">（例: チェックボックスやトグルスイッチをここに配置）</p>

      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-btn :href="createBookingUrl">戻る</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="primary" @click="finalizeBooking">予約を確定する</v-btn>
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
  date: string;
  time: string;
  staff_id: number;
  staff_name: string;
  service_id: number;
  service_name: string;
  booker_name: string;
  booker_email: string | null;
  booker_tel: string | null;
}

const props = defineProps<{
  shopId: string;
  bookingDetails: string; // JSON文字列として受け取る
}>();

const parsedBookingDetails = ref<BookingDetails | null>(null);

const createBookingUrl = computed(() => `/shops/${props.shopId}/staff/bookings/new`);

const finalizeBooking = () => {
  // ここで最終的な予約確定処理（DB保存など）を行うAPIコールを想定
  // 現時点ではダミーのアラート
  alert('予約を確定しました。（ダミー処理）');
  // 確定後は予約一覧画面などへ遷移
  window.location.href = `/shops/${props.shopId}/staff/bookings`;
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
