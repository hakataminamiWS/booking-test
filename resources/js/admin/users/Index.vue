<template>
  <v-container>
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <span>オーナー権限設定</span>
      </v-card-title>
      <v-card-text>
        <div class="mb-4">
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                name="public_id"
                label="Public IDで検索"
                v-model="publicIdModel"
                hide-details
                dense
                @keydown.enter="fetchUsers(1)"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="2">
              <v-btn type="button" color="primary" @click="fetchUsers(1)">検索</v-btn>
            </v-col>
          </v-row>
        </div>

        <v-pagination
          :model-value="localUsers.current_page"
          :length="localUsers.last_page"
          :total-visible="5"
          @update:modelValue="page => fetchUsers(page)"
        ></v-pagination>

        <div class="mb-2">
          全 {{ localUsers.total }} 件中 {{ localUsers.from }}-{{ localUsers.to }} 件表示
        </div>
        <v-data-table
          :headers="headers"
          :items="localUsers.data"
          class="elevation-1"
          items-per-page="-1"
          hide-default-footer
        >
          <template v-slot:item.public_id="{ item }">
            <a :href="`/admin/users/${item.public_id}`">{{ item.public_id }}</a>
          </template>
          <template v-slot:item.is_owner="{ item }">
            <v-chip :color="item.is_owner ? 'primary' : ''" dark>
              {{ item.is_owner ? 'Owner' : 'User' }}
            </v-chip>
          </template>
          <template v-slot:item.actions="{ item }">
            <v-btn :href="`/admin/users/${item.public_id}/edit`" small color="primary">
              編集
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';

export default defineComponent({
  props: {
    users: {
      type: Object,
      required: true,
    },
    publicId: {
      type: String,
      default: '',
    },
  },
  setup(props) {
    const localUsers = ref(props.users);
    const publicIdModel = ref(props.publicId);

    const headers = [
      { title: 'Public ID', value: 'public_id', key: 'public_id' },
      { title: '役割', value: 'is_owner', key: 'is_owner' },
      { title: '登録日時', value: 'created_at', key: 'created_at' },
      { title: '操作', value: 'actions', sortable: false },
    ];

    const fetchUsers = async (page = 1) => {
      const params = new URLSearchParams();
      params.append('page', page.toString());
      if (publicIdModel.value) {
        params.append('public_id', publicIdModel.value);
      }

      const queryString = params.toString();
      const url = `/admin/users?${queryString}`;
      const apiUrl = `/api/admin/users?${queryString}`;

      history.pushState(null, '', url);

      try {
        const response = await axios.get(apiUrl);
        localUsers.value = response.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    };

    onMounted(() => {
      const urlParams = new URLSearchParams(window.location.search);
      const publicId = urlParams.get('public_id');

      if (publicId) {
        publicIdModel.value = publicId;
      }
    });

    return {
      headers,
      localUsers,
      publicIdModel,
      fetchUsers,
    };
  },
});
</script>
