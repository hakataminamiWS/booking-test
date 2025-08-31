<template>
  <v-container v-if="parsedStaffs">
    <v-card class="mx-auto" max-width="800">
      <v-card-title class="text-h5 font-weight-bold">スタッフ管理 (店舗ID: {{ shopId }})</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="parsedStaffs"
          item-value="id"
          class="elevation-1"
          no-data-text="スタッフが登録されていません。"
        >
          <template v-slot:item.actions="{ item }">
            <v-btn
              color="primary"
              variant="text"
              size="small"
              :href="staffEditUrl(item.id)"
            >
              編集
            </v-btn>
            <v-btn
              color="error"
              variant="text"
              size="small"
              @click="deleteStaff(item.id)"
            >
              削除
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
      <v-card-actions>
        <v-btn :href="shopDetailUrl">店舗詳細に戻る</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="success">新規スタッフ追加</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
  <v-container v-else>
    <p class="text-center">スタッフ情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface Staff {
  id: number;
  name: string;
  role: string;
}

const props = defineProps<{
  shopId: string;
  staffs: string; // JSON文字列として受け取る
}>();

const parsedStaffs = ref<Staff[]>([]);

const headers = [
  { title: 'ID', key: 'id' },
  { title: '名前', key: 'name' },
  { title: '役割', key: 'role' },
  { title: '操作', key: 'actions', sortable: false },
];

const shopDetailUrl = computed(() => `/owner/shops/${props.shopId}`);
const staffEditUrl = (staffId: number) => `/owner/shops/${props.shopId}/staff/${staffId}/edit`;

const deleteStaff = (staffId: number) => {
  if (confirm(`スタッフID: ${staffId} を削除しますか？`)) {
    console.log(`Deleting staff ${staffId}`);
    alert(`スタッフID: ${staffId} を削除しました。（ダミー）`);
  }
};

onMounted(() => {
  try {
    parsedStaffs.value = JSON.parse(props.staffs);
  } catch (e) {
    console.error("Failed to parse staffs data:", e);
    parsedStaffs.value = [];
  }
});
</script>
