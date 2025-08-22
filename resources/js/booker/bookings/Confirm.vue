<template>
  <v-container>
    <v-card max-width="600" class="mx-auto">
      <v-card-title class="text-h5">ご予約内容の確認</v-card-title>
      <v-card-text>
        <p class="mb-4">以下の内容でよろしければ、「予約を確定する」ボタンを押してください。</p>
        <v-list lines="one">
          <v-list-item>
            <v-list-item-title><strong>日時:</strong> {{ bookingData.date }} {{ bookingData.time }}</v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title><strong>担当者:</strong> {{ bookingData.staff_name }}</v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title><strong>サービス:</strong> {{ bookingData.service_name }}</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-card-text>
      <v-card-actions>
        <v-btn color="secondary" @click="goBack">修正する</v-btn>
        <v-spacer></v-spacer>
        <form :action="action" method="POST">
          <input type="hidden" name="_token" :value="csrfToken">
          <v-btn type="submit" color="primary">予約を確定する</v-btn>
        </form>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

interface BookingData {
  date: string;
  time: string;
  staff_name: string;
  service_name: string;
}

const props = defineProps({
  action: {
    type: String,
    required: true,
  },
  bookingData: {
    type: Object as () => BookingData,
    required: true,
  },
});

const csrfToken = ref('');

onMounted(() => {
  csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
});

const goBack = () => {
  history.back();
};
</script>

<style scoped>
/* 必要に応じてスタイルを追加 */
</style>