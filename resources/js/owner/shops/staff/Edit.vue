<template>
  <v-container v-if="parsedStaffDetails">
    <v-card class="mx-auto" max-width="600">
      <v-card-title class="text-h5 font-weight-bold">スタッフ編集 (ID: {{ staffId }})</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-list lines="one">
          <v-list-item title="名前" :subtitle="parsedStaffDetails.name"></v-list-item>
          <v-list-item title="役割" :subtitle="parsedStaffDetails.role"></v-list-item>
          <v-list-item title="メール" :subtitle="parsedStaffDetails.email"></v-list-item>
        </v-list>
        <v-text-field v-model="parsedStaffDetails.name" label="名前"></v-text-field>
        <v-text-field v-model="parsedStaffDetails.role" label="役割"></v-text-field>
        <v-text-field v-model="parsedStaffDetails.email" label="メール"></v-text-field>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-btn :href="staffListUrl">スタッフ一覧に戻る</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="primary" @click="saveStaff">保存</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
  <v-container v-else>
    <p class="text-center">スタッフ情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface StaffDetails {
  id: number;
  name: string;
  role: string;
  email: string;
}

const props = defineProps<{
  shopId: string;
  staffId: string;
  staffDetails: string; // JSON文字列として受け取る
}>();

const parsedStaffDetails = ref<StaffDetails | null>(null);

const staffListUrl = computed(() => `/owner/shops/${props.shopId}/staff`);

const saveStaff = () => {
  alert(`スタッフID: ${props.staffId} の情報を保存しました。（ダミー）`);
  console.log("Saving staff details:", parsedStaffDetails.value);
};

onMounted(() => {
  try {
    parsedStaffDetails.value = JSON.parse(props.staffDetails);
  } catch (e) {
    console.error("Failed to parse staff details:", e);
    parsedStaffDetails.value = null;
  }
});
</script>
