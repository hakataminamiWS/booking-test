<template>
  <v-container>
    <a href="/admin/users" class="text-decoration-none d-block mb-4">&lt; オーナー権限設定一覧へ戻る</a>
    <v-card>
      <v-card-title>
        オーナー権限の編集: {{ user.public_id }}
      </v-card-title>
      <v-card-text>
        <form :action="`/admin/users/${user.public_id}`" method="POST">
          <input type="hidden" name="_token" :value="csrfToken">
          <input type="hidden" name="_method" value="PUT">
          
          <!-- Form submission value -->
          <input type="hidden" name="is_owner" :value="isOwnerModel ? 1 : 0">

          <!-- UI Switch -->
          <v-switch
            label="オーナー権限"
            v-model="isOwnerModel"
            color="primary"
            inset
          ></v-switch>

          <div class="d-flex justify-end">
            <v-btn :href="`/admin/users/${user.public_id}`" class="mr-2">
              キャンセル
            </v-btn>
            <v-btn type="submit" color="primary">
              更新する
            </v-btn>
          </div>
        </form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';

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
    csrfToken: {
      type: String,
      required: true,
    },
  },
  setup(props) {
    const isOwnerModel = ref(props.isOwner);

    return {
      isOwnerModel,
    };
  },
});
</script>
