<template>
  <v-container>
    <a href="/admin/users" class="text-decoration-none d-block mb-4">&lt; オーナー権限設定一覧へ戻る</a>
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <span>オーナー権限・契約詳細</span>
        <v-btn :href="`/admin/users/${user.public_id}/edit`" color="primary">
          権限を編集する
        </v-btn>
      </v-card-title>
      <v-card-text>
        <v-list dense>
          <v-list-item>
            <v-list-item-content>
              <v-list-item-title>Public ID</v-list-item-title>
              <v-list-item-subtitle>{{ user.public_id }}</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-list-item>
            <v-list-item-content>
              <v-list-item-title>役割</v-list-item-title>
              <v-list-item-subtitle>
                <v-chip :color="isOwner ? 'primary' : ''" dark>
                  {{ isOwner ? 'Owner' : 'User' }}
                </v-chip>
              </v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-list-item>
            <v-list-item-content>
              <v-list-item-title>登録日時</v-list-item-title>
              <v-list-item-subtitle>{{ user.created_at }}</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
        </v-list>

        <v-divider class="my-4"></v-divider>

        <div class="d-flex justify-space-between align-center mb-2">
          <h3 class="text-h6">契約情報</h3>
          <v-btn v-if="isOwner && hasContract" :href="`/admin/contracts/${contract.id}/edit`">契約を編集する</v-btn>
        </div>
        
        <div v-if="isOwner">
          <div v-if="hasContract">
            <v-list dense>
              <v-list-item>
                <v-list-item-content>
                  <v-list-item-title>契約名</v-list-item-title>
                  <v-list-item-subtitle>{{ contract.name }}</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
              <v-list-item>
                <v-list-item-content>
                  <v-list-item-title>契約期間</v-list-item-title>
                  <v-list-item-subtitle>{{ contract.start_date }} 〜 {{ contract.end_date }}</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
              <v-list-item>
                <v-list-item-content>
                  <v-list-item-title>契約ステータス</v-list-item-title>
                  <v-list-item-subtitle>{{ contract.status }}</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
              <v-list-item>
                <v-list-item-content>
                  <v-list-item-title>店舗作成上限</v-list-item-title>
                  <v-list-item-subtitle>{{ contract.max_shops }}</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-list>
          </div>
          <div v-else>
            <p>このオーナーはまだ契約がありません。</p>
            <v-btn :href="`/admin/contracts/create?user_public_id=${user.public_id}`" color="primary">契約を作成する</v-btn>
          </div>
        </div>
        <div v-else>
          <p>オーナーではないため、契約情報はありません。</p>
        </div>

      </v-card-text>
    </v-card>
  </v-container>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    user: {
      type: Object,
      required: true,
    },
    isOwner: {
      type: Boolean,
      required: true,
    },
    hasContract: {
      type: Boolean,
      required: true,
    },
    contract: {
      type: Object,
      default: null,
    },
  },
});
</script>
