<template>
  <v-container v-if="parsedShopDetails">
    <v-card class="mx-auto" max-width="600">
      <v-card-title class="text-h5 font-weight-bold">店舗詳細 (ID: {{ shopId }})</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-list lines="one">
          <v-list-item title="店舗名" :subtitle="parsedShopDetails.name"></v-list-item>
          <v-list-item title="住所" :subtitle="parsedShopDetails.address"></v-list-item>
          <v-list-item title="電話番号" :subtitle="parsedShopDetails.phone"></v-list-item>
        </v-list>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-btn :href="shopsListUrl">店舗一覧に戻る</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="primary" :href="staffManageUrl">スタッフ管理</v-btn>
        <v-btn color="secondary">店舗情報編集</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
  <v-container v-else>
    <p class="text-center">店舗情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface ShopDetails {
  id: number;
  name: string;
  address: string;
  phone: string;
}

const props = defineProps<{
  shopId: string;
  shopDetails: string; // JSON文字列として受け取る
}>();

const parsedShopDetails = ref<ShopDetails | null>(null);

const shopsListUrl = computed(() => `/owner/shops`);
const staffManageUrl = computed(() => `/owner/shops/${props.shopId}/staff`);

onMounted(() => {
  try {
    parsedShopDetails.value = JSON.parse(props.shopDetails);
  } catch (e) {
    console.error("Failed to parse shop details:", e);
    parsedShopDetails.value = null;
  }
});
</script>
