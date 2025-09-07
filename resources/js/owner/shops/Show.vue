<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4">店舗詳細</h1>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <v-card v-if="shop">
          <v-card-title>{{ shop.name }}</v-card-title>
          <v-card-text>
            <p><strong>ID:</strong> {{ shop.id }}</p>
            <p><strong>住所:</strong> {{ shop.address }}</p>
            <p><strong>電話番号:</strong> {{ shop.phone_number }}</p>
            <p><strong>営業時間:</strong> {{ shop.opening_time }} - {{ shop.closing_time }}</p>
            <p><strong>定休日:</strong> {{ shop.regular_holidays ? shop.regular_holidays.join(', ') : 'なし' }}</p>
            <p><strong>予約受付設定:</strong> {{ shop.reservation_acceptance_settings ? JSON.stringify(shop.reservation_acceptance_settings) : 'なし' }}</p>
            <p><strong>作成日時:</strong> {{ shop.created_at }}</p>
            <p><strong>更新日時:</strong> {{ shop.updated_at }}</p>
          </v-card-text>
          <v-card-actions>
            <v-btn color="primary" :to="{ name: 'owner.shops.edit', params: { shop: shop.id } }">編集</v-btn>
            <v-btn :to="{ name: 'owner.shops.index' }" class="ml-2">一覧に戻る</v-btn>
          </v-card-actions>
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

interface Shop {
  id: number;
  name: string;
  address: string;
  phone_number: string;
  opening_time: string;
  closing_time: string;
  regular_holidays: string[];
  reservation_acceptance_settings: any;
  created_at: string;
  updated_at: string;
}

const shop = ref<Shop | null>(null);

onMounted(() => {
  // Bladeから渡されたデータを取得
  const shopDataElement = document.getElementById('owner-shops-show');
  if (shopDataElement && shopDataElement.dataset.shop) {
    shop.value = JSON.parse(shopDataElement.dataset.shop);
  } else {
    // データが渡されなかった場合、APIを叩く（本来は不要だが念のため）
    const shopId = route.params.shop as string;
    if (shopId) {
      axios.get(`/owner/shops/${shopId}`).then(response => {
        shop.value = response.data;
      }).catch(error => {
        console.error('店舗詳細の取得に失敗しました:', error);
        shop.value = null;
      });
    }
  }
});
</script>