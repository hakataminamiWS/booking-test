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
            <p><strong>削除日時:</strong> {{ shop.deleted_at ?? 'なし' }}</p>
          </v-card-text>
          <v-card-actions>
            <v-btn color="primary" :to="{ name: 'admin.shops.edit', params: { shop: shop.id } }">編集</v-btn>
            <v-btn color="error" @click="softDeleteShop(shop.id)" class="ml-2">削除 (論理)</v-btn>
            <v-btn v-if="shop.deleted_at" color="red-darken-4" @click="forceDeleteShop(shop.id)" class="ml-2">削除 (物理)</v-btn>
            <v-btn :to="{ name: 'admin.shops.index' }" class="ml-2">一覧に戻る</v-btn>
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
  deleted_at: string | null;
}

const shop = ref<Shop | null>(null);

onMounted(async () => {
  const shopId = route.params.shop as string;
  if (shopId) {
    try {
      const response = await axios.get(`/admin/shops/${shopId}`);
      shop.value = response.data.shop;
    } catch (error) {
      console.error('店舗詳細の取得に失敗しました:', error);
      shop.value = null;
    }
  }
});

const softDeleteShop = async (id: number) => {
  if (confirm('本当にこの店舗を論理削除しますか？')) {
    try {
      await axios.delete(`/admin/shops/${id}`);
      router.push({ name: 'admin.shops.index' }); // 削除後一覧へ
    } catch (error) {
      console.error('店舗の論理削除に失敗しました:', error);
      alert('店舗の論理削除に失敗しました。');
    }
  }
};

const forceDeleteShop = async (id: number) => {
  if (confirm('本当にこの店舗を物理削除しますか？この操作は元に戻せません。')) {
    try {
      await axios.delete(`/admin/shops/${id}/force-delete`);
      router.push({ name: 'admin.shops.index' }); // 削除後一覧へ
    } catch (error) {
      console.error('店舗の物理削除に失敗しました:', error);
      alert('店舗の物理削除に失敗しました。');
    }
  }
};
</script>