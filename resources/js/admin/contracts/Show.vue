<template>
  <v-container v-if="contractDetails">
    <v-card class="mx-auto" max-width="600">
      <v-card-title class="text-h5 font-weight-bold">契約詳細 (ID: {{ contractId }})</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-list lines="one">
          <v-list-item title="オーナー名" :subtitle="contractDetails.user.owner.name"></v-list-item>
          <v-list-item title="最大店舗数" :subtitle="contractDetails.max_shops"></v-list-item>
          <v-list-item title="ステータス" :subtitle="contractDetails.status"></v-list-item>
          <v-list-item title="契約開始日" :subtitle="contractDetails.start_date"></v-list-item>
          <v-list-item title="契約終了日" :subtitle="contractDetails.end_date"></v-list-item>
        </v-list>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-btn :href="contractsListUrl">契約一覧に戻る</v-btn>
        <v-spacer></v-spacer>
        <v-btn color="primary" :href="editUrl">契約情報編集</v-btn>
        <v-btn color="error" @click="dialog = true">削除</v-btn>
      </v-card-actions>
    </v-card>

    <v-dialog v-model="dialog" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">本当にこの契約を削除しますか？</v-card-title>
        <v-card-text>
          この操作は元に戻せません。削除を確認するには、オーナーの公開ID (<b>{{ ownerPublicId }}</b>) を入力してください。
          <v-text-field
            v-model="confirmationText"
            label="オーナー公開ID"
            class="mt-4"
          ></v-text-field>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="dialog = false">キャンセル</v-btn>
          <form :action="deleteUrl" method="POST">
            <input type="hidden" name="_token" :value="csrfToken">
            <input type="hidden" name="_method" value="DELETE">
            <v-btn
              color="error"
              type="submit"
              :disabled="!isDeleteConfirmed"
            >
              削除を実行
            </v-btn>
          </form>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-container>
  <v-container v-else>
    <p class="text-center">契約情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

interface ContractDetails {
  id: number;
  user: { public_id: string, owner: { name: string } };
  status: string;
  start_date: string;
  end_date: string;
  max_shops: number;
}

const props = defineProps<{
  contractId: string;
  contractDetails: ContractDetails;
  csrfToken: string;
}>();

const dialog = ref(false);
const confirmationText = ref('');

const ownerPublicId = computed(() => props.contractDetails.user.public_id);
const isDeleteConfirmed = computed(() => confirmationText.value === ownerPublicId.value);

const contractsListUrl = `/admin/contracts`;
const editUrl = computed(() => `/admin/contracts/${props.contractId}/edit`);
const deleteUrl = computed(() => `/admin/contracts/${props.contractId}`);

</script>
