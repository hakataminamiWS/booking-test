<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4">新規店舗登録</h1>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <v-card>
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
              <v-select
                v-model="form.owner_id"
                :items="owners"
                item-title="name"
                item-value="id"
                label="オーナー"
                required
              ></v-select>
              <v-btn type="submit" color="primary">登録</v-btn>
              <v-btn :to="{ name: 'admin.shops.index' }" class="ml-2">キャンセル</v-btn>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

interface Owner {
  id: number;
  name: string;
}

const form = ref({
  name: '',
  address: '',
  phone_number: '',
  opening_time: '',
  closing_time: '',
  regular_holidays: [] as string[],
  reservation_acceptance_settings: '{}',
  owner_id: null as number | null,
});

const owners = ref<Owner[]>([]);

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
  try {
    const response = await axios.get('/admin/owners');
    owners.value = response.data.owners;
  } catch (error) {
    console.error('オーナーリストの取得に失敗しました:', error);
  }
});

const submitForm = async () => {
  try {
    const payload = {
      ...form.value,
      regular_holidays: form.value.regular_holidays,
      reservation_acceptance_settings: JSON.parse(form.value.reservation_acceptance_settings),
    };
    const response = await axios.post('/admin/shops', payload);
    router.push({ name: 'admin.shops.show', params: { shop: response.data.shop.id } }); // 登録後詳細へ
  } catch (error) {
    console.error('店舗登録に失敗しました:', error);
    alert('店舗登録に失敗しました。入力内容を確認してください。');
  }
};
</script>