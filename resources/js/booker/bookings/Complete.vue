<template>
  <v-container v-if="parsedBookingDetails">
    <v-card class="mx-auto" max-width="600">
      <v-card-title class="text-h5 text-center py-4 bg-primary">
        ご予約ありがとうございます
      </v-card-title>
      <v-card-text class="py-5">
        <p class="text-center mb-6">以下の内容でご予約を承りました。</p>
        
        <v-list lines="one">
          <v-list-item 
            title="日付"
            :subtitle="parsedBookingDetails.date"
            prepend-icon="mdi-calendar"
          ></v-list-item>
          <v-list-item 
            title="時間"
            :subtitle="parsedBookingDetails.time"
            prepend-icon="mdi-clock-outline"
          ></v-list-item>
          <v-list-item 
            title="担当者"
            :subtitle="parsedBookingDetails.staff_name"
            prepend-icon="mdi-account-outline"
          ></v-list-item>
          <v-list-item 
            title="サービス"
            :subtitle="parsedBookingDetails.service_name"
            prepend-icon="mdi-auto-fix"
          ></v-list-item>
        </v-list>

      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-btn :href="topPageUrl">店舗トップに戻る</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="primary" :href="myBookingsUrl">予約一覧へ</v-btn>
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
  staff_name: string;
  service_name: string;
}

const props = defineProps<{
  shopId: string;
  bookingDetails: string | null; // propsで文字列として受け取る
}>();

const parsedBookingDetails = ref<BookingDetails | null>(null); // パース後のデータを格納

const topPageUrl = computed(() => `/shops/${props.shopId}`);
const myBookingsUrl = computed(() => `/shops/${props.shopId}/bookings`);

onMounted(() => {
  if (props.bookingDetails) {
    try {
      parsedBookingDetails.value = JSON.parse(props.bookingDetails);
    } catch (e) {
      console.error("Failed to parse booking details:", e);
      parsedBookingDetails.value = null;
    }
  }
});

</script>