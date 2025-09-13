<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4">店舗編集</h1>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <v-card v-if="form.id">
          <v-card-text>
            <v-form @submit.prevent="submitForm">
              <v-text-field v-model="form.name" label="店舗名" required></v-text-field>
              <v-text-field v-model="form.address" label="住所"></v-text-field>
              <v-text-field v-model="form.phone_number" label="電話番号"></v-text-field>
              <v-text-field v-model="form.opening_time" label="営業時間開始 (HH:mm)"></v-text-field>
              <v-text-field v-model="form.closing_time" label="営業時間終了 (HH:mm)"></v-text-field>
              <v-select
                v-model="form.regular_holidays"
                :items="daysOfWeek"
                label="定休日"
                multiple
                chips
              ></v-select>
              <v-textarea v-model="form.reservation_acceptance_settings" label="予約受付設定 (JSON)"></v-textarea>
              <v-btn type="submit" color="primary">更新</v-btn>
              <v-btn :to="{ name: 'admin.shops.show', params: { shop: form.id } }" class="ml-2">キャンセル</v-btn>
            </v-form>
          </v-card-text>
        </v-card>
        <v-alert v-else type="info">店舗情報が見つかりません。</v-alert>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const form = ref({
  id: null as number | null,
  name: '',
  address: '',
  phone_number: '',
  opening_time: '',
  closing_time: '',
  regular_holidays: [] as string[],
  reservation_acceptance_settings: '{}',
});

const daysOfWeek = [
  { title: '月曜日', value: 'monday' },
  { title: '火曜日', value: 'tuesday' },
  { title: '水曜日', value: 'wednesday' },
  { title: '木曜日', value: 'thursday' },
  { title: '金曜日', value: 'friday' },
  { title: '土曜日', value: 'saturday' },
  { title: '日曜日', value: 'sunday' },
];

onMounted(async () => {
  const shopId = route.params.shop as string;
  if (shopId) {
    try {
      const response = await axios.get(`/admin/shops/${shopId}`);
      const shop = response.data.shop;
      form.value.id = shop.id;
      form.value.name = shop.name;
      form.value.address = shop.address;
      form.value.phone_number = shop.phone_number;
      form.value.opening_time = shop.opening_time ? shop.opening_time.substring(0, 5) : '';
      form.value.closing_time = shop.closing_time ? shop.closing_time.substring(0, 5) : '';
      form.value.regular_holidays = shop.regular_holidays || [];
      form.value.reservation_acceptance_settings = shop.reservation_acceptance_settings ? JSON.stringify(shop.reservation_acceptance_settings, null, 2) : '{}';
    } catch (error) {
      console.error('店舗詳細の取得に失敗しました:', error);
      form.value.id = null;
    }
  }
});

const submitForm = async () => {
  try {
    const payload = {
      ...form.value,
      regular_holidays: form.value.regular_holidays,
      reservation_acceptance_settings: JSON.parse(form.value.reservation_acceptance_settings),
      _method: 'PUT', // LaravelのPUTメソッドをエミュレート
    };
    await axios.post(`/admin/shops/${form.value.id}`, payload);
    router.push({ name: 'admin.shops.show', params: { shop: form.value.id } }); // 更新後詳細へ
  } catch (error) {
    console.error('店舗更新に失敗しました:', error);
    alert('店舗更新に失敗しました。入力内容を確認してください。');
  }
};
</script>