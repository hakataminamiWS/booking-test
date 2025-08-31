<template>
  <v-container v-if="parsedShops">
    <v-card class="mx-auto" max-width="800">
      <v-card-title class="text-h5 font-weight-bold">店舗一覧</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="parsedShops"
          item-value="id"
          class="elevation-1"
          no-data-text="登録店舗がありません。"
        >
          <template v-slot:item.actions="{ item }">
            <v-btn
              color="primary"
              variant="text"
              size="small"
              :href="shopDetailUrl(item.id)"
            >
              詳細
            </v-btn>
            <v-btn
              color="secondary"
              variant="text"
              size="small"
              :href="staffManageUrl(item.id)"
            >
              スタッフ管理
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="success">新規店舗登録</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
  <v-container v-else>
    <p class="text-center">店舗情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

interface Shop {
  id: number;
  name: string;
  status: string;
}

const props = defineProps<{
  shops: string; // JSON文字列として受け取る
}>();

const parsedShops = ref<Shop[]>([]);

const headers = [
  { title: 'ID', key: 'id' },
  { title: '店舗名', key: 'name' },
  { title: 'ステータス', key: 'status' },
  { title: '操作', key: 'actions', sortable: false },
];

const shopDetailUrl = (shopId: number) => `/owner/shops/${shopId}`;
const staffManageUrl = (shopId: number) => `/owner/shops/${shopId}/staff`;

onMounted(() => {
  try {
    parsedShops.value = JSON.parse(props.shops);
  } catch (e) {
    console.error("Failed to parse shops data:", e);
    parsedShops.value = [];
  }
});
</script>
