<template>
  <v-container v-if="parsedOwners">
    <v-card class="mx-auto" max-width="800">
      <v-card-title class="text-h5 font-weight-bold">オーナー管理</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="parsedOwners"
          item-value="id"
          class="elevation-1"
          no-data-text="オーナー情報がありません。"
        >
          <template v-slot:item.actions="{ item }">
            <v-btn
              color="primary"
              variant="text"
              size="small"
              :href="ownerDetailUrl(item.id)"
            >
              詳細
            </v-btn>
            <v-btn
              color="error"
              variant="text"
              size="small"
              @click="deleteOwner(item.id)"
            >
              削除
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="success">新規オーナー追加</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
  <v-container v-else>
    <p class="text-center">オーナー情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

interface Owner {
  id: number;
  name: string;
  contract_status: string;
}

const props = defineProps<{
  owners: string; // JSON文字列として受け取る
}>();

const parsedOwners = ref<Owner[]>([]);

const headers = [
  { title: 'ID', key: 'id' },
  { title: '名前', key: 'name' },
  { title: '契約ステータス', key: 'contract_status' },
  { title: '操作', key: 'actions', sortable: false },
];

const ownerDetailUrl = (ownerId: number) => `/admin/owners/${ownerId}`;

const deleteOwner = (ownerId: number) => {
  if (confirm(`オーナーID: ${ownerId} を削除しますか？`)) {
    console.log(`Deleting owner ${ownerId}`);
    alert(`オーナーID: ${ownerId} を削除しました。（ダミー）`);
  }
};

onMounted(() => {
  try {
    parsedOwners.value = JSON.parse(props.owners);
  } catch (e) {
    console.error("Failed to parse owners data:", e);
    parsedOwners.value = [];
  }
});
</script>
