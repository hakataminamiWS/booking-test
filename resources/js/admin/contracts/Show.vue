<template>
  <v-container v-if="parsedContractDetails">
    <v-card class="mx-auto" max-width="600">
      <v-card-title class="text-h5 font-weight-bold">契約詳細 (ID: {{ contractId }})</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-list lines="one">
          <v-list-item title="オーナー名" :subtitle="parsedContractDetails.owner_name"></v-list-item>
          <v-list-item title="プラン" :subtitle="parsedContractDetails.plan"></v-list-item>
          <v-list-item title="ステータス" :subtitle="parsedContractDetails.status"></v-list-item>
          <v-list-item title="期限" :subtitle="parsedContractDetails.expires_at"></v-list-item>
          <v-list-item title="備考" :subtitle="parsedContractDetails.notes"></v-list-item>
        </v-list>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-btn :href="contractsListUrl">契約一覧に戻る</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="primary">契約情報編集</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
  <v-container v-else>
    <p class="text-center">契約情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface ContractDetails {
  id: number;
  owner_name: string;
  plan: string;
  status: string;
  expires_at: string;
  notes: string;
}

const props = defineProps<{
  contractId: string;
  contractDetails: string; // JSON文字列として受け取る
}>();

const parsedContractDetails = ref<ContractDetails | null>(null);

const contractsListUrl = computed(() => `/admin/contracts`);

onMounted(() => {
  try {
    parsedContractDetails.value = JSON.parse(props.contractDetails);
  } catch (e) {
    console.error("Failed to parse contract details:", e);
    parsedContractDetails.value = null;
  }
});
</script>
