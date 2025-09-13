<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4">管理者向け店舗一覧</h1>
        <v-btn color="primary" :to="{ name: 'admin.shops.create' }">新規店舗登録</v-btn>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title>店舗リスト</v-card-title>
          <v-card-text>
            <v-table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>店舗名</th>
                  <th>ステータス</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="shop in shops" :key="shop.id">
                  <td>{{ shop.id }}</td>
                  <td>{{ shop.name }}</td>
                  <td>
                    <v-chip :color="shop.deleted_at ? 'error' : 'success'">
                      {{ shop.deleted_at ? '削除済み' : '有効' }}
                    </v-chip>
                  </td>
                  <td>
                    <v-btn small color="info" :to="{ name: 'admin.shops.show', params: { shop: shop.id } }">詳細</v-btn>
                    <v-btn small color="warning" :to="{ name: 'admin.shops.edit', params: { shop: shop.id } }" class="ml-2">編集</v-btn>
                    <v-btn small color="error" @click="softDeleteShop(shop.id)" class="ml-2">削除 (論理)</v-btn>
                    <v-btn v-if="shop.deleted_at" small color="red-darken-4" @click="forceDeleteShop(shop.id)" class="ml-2">削除 (物理)</v-btn>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

interface Shop {
  id: number;
  name: string;
  address: string;
  phone_number: string;
  opening_time: string;
  closing_time: string;
  regular_holidays: string[];
  reservation_acceptance_settings: any;
  deleted_at: string | null;
}

const shops = ref<Shop[]>([]);

const fetchShops = async () => {
  try {
    const response = await axios.get('/admin/shops');
    shops.value = response.data.shops;
  } catch (error) {
    console.error('店舗データの取得に失敗しました:', error);
  }
};

const softDeleteShop = async (id: number) => {
  if (confirm('本当にこの店舗を論理削除しますか？')) {
    try {
      await axios.delete(`/admin/shops/${id}`);
      fetchShops(); // 更新されたリストを再取得
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
      fetchShops(); // 更新されたリストを再取得
    } catch (error) {
      console.error('店舗の物理削除に失敗しました:', error);
      alert('店舗の物理削除に失敗しました。');
    }
  }
};

onMounted(() => {
  fetchShops();
});
</script>