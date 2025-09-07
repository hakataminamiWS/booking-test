<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4">店舗一覧</h1>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title>店舗リスト</v-card-title>
          <v-card-text>
            <v-list>
              <v-list-item v-for="shop in shops" :key="shop.id" :to="{ name: 'owner.shops.show', params: { shop: shop.id } }">
                <v-list-item-title>{{ shop.name }}</v-list-item-title>
                <v-list-item-subtitle>{{ shop.address }}</v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

interface Shop {
  id: number;
  name: string;
  address: string;
  phone_number: string;
  opening_time: string;
  closing_time: string;
  regular_holidays: string[];
  reservation_acceptance_settings: any;
}

const shops = ref<Shop[]>([]);

onMounted(() => {
  const el = document.getElementById('owner-shops-index');
  if (el && el.dataset.shops) {
    shops.value = JSON.parse(el.dataset.shops);
  }
});
</script>