<template>
  <v-container v-if="parsedOwnerDetails">
    <v-card class="mx-auto" max-width="600">
      <v-card-title class="text-h5 font-weight-bold">オーナー詳細 (ID: {{ ownerId }})</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-list lines="one">
          <v-list-item title="名前" :subtitle="parsedOwnerDetails.name"></v-list-item>
          <v-list-item title="メール" :subtitle="parsedOwnerDetails.email"></v-list-item>
          <v-list-item title="契約ステータス" :subtitle="parsedOwnerDetails.contract_status"></v-list-item>
        </v-list>
        <h4 class="text-h6 mt-4">管理店舗</h4>
        <v-list density="compact">
          <v-list-item v-for="shop in parsedOwnerDetails.shops" :key="shop.id">
            <v-list-item-title>{{ shop.name }}</v-list-item-title>
            <template v-slot:append>
              <v-btn variant="text" size="small" :href="`/owner/shops/${shop.id}`">詳細</v-btn>
            </template>
          </v-list-item>
        </v-list>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-btn :href="ownersListUrl">オーナー一覧に戻る</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="primary">オーナー情報編集</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
  <v-container v-else>
    <p class="text-center">オーナー情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface OwnerDetails {
  id: number;
  name: string;
  email: string;
  contract_status: string;
  shops: Array<{ id: number; name: string }>;
}

const props = defineProps<{
  ownerId: string;
  ownerDetails: string; // JSON文字列として受け取る
}>();

const parsedOwnerDetails = ref<OwnerDetails | null>(null);

const ownersListUrl = computed(() => `/admin/owners`);

onMounted(() => {
  try {
    parsedOwnerDetails.value = JSON.parse(props.ownerDetails);
  } catch (e) {
    console.error("Failed to parse owner details:", e);
    parsedOwnerDetails.value = null;
  }
});
</script>
