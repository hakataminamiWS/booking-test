<template>
  <v-container v-if="parsedContracts">
    <v-card class="mx-auto" max-width="800">
      <v-card-title class="text-h5 font-weight-bold">契約管理</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="parsedContracts"
          item-value="id"
          class="elevation-1"
          no-data-text="契約情報がありません。"
        >
          <template v-slot:item.actions="{ item }">
            <v-btn
              color="primary"
              variant="text"
              size="small"
              @click="viewContract(item.id)"
            >
              詳細
            </v-btn>
            <v-btn
              color="secondary"
              variant="text"
              size="small"
              @click="updateContractStatus(item.id)"
            >
              ステータス更新
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="success">新規契約追加</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
  <v-container v-else>
    <p class="text-center">契約情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

interface Contract {
  id: number;
  plan: string;
  status: string;
  expires_at: string;
}

const props = defineProps<{
  contracts: string; // JSON文字列として受け取る
}>();

const parsedContracts = ref<Contract[]>([]);

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'プラン', key: 'plan' },
  { title: 'ステータス', key: 'status' },
  { title: '期限', key: 'expires_at' },
  { title: '操作', key: 'actions', sortable: false },
];

const viewContract = (contractId: number) => {
  alert(`契約ID: ${contractId} の詳細を表示します。（ダミー）`);
  console.log(`Viewing contract ${contractId}`);
};

const updateContractStatus = (contractId: number) => {
  alert(`契約ID: ${contractId} のステータスを更新します。（ダミー）`);
  console.log(`Updating status for contract ${contractId}`);
};

onMounted(() => {
  try {
    parsedContracts.value = JSON.parse(props.contracts);
  } catch (e) {
    console.error("Failed to parse contracts data:", e);
    parsedContracts.value = [];
  }
});
</script>
