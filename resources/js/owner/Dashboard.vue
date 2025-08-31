<template>
  <v-container v-if="parsedDashboardData">
    <v-card class="mx-auto" max-width="800">
      <v-card-title class="text-h5 font-weight-bold">オーナーダッシュボード</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <h3 class="text-h6 mb-2">概要</h3>
        <v-row>
          <v-col cols="4">
            <v-card variant="outlined">
              <v-card-text class="text-center">
                <div class="text-h5">{{ parsedDashboardData.total_shops }}</div>
                <div class="text-subtitle-1">登録店舗数</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="4">
            <v-card variant="outlined">
              <v-card-text class="text-center">
                <div class="text-h5">{{ parsedDashboardData.active_contracts }}</div>
                <div class="text-subtitle-1">有効契約数</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="4">
            <v-card variant="outlined">
              <v-card-text class="text-center">
                <div class="text-h5">{{ parsedDashboardData.pending_bookings_across_shops }}</div>
                <div class="text-subtitle-1">全店舗のペンディング予約</div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <v-divider class="my-4"></v-divider>

        <h3 class="text-h6 mb-2">店舗別サマリー</h3>
        <v-list density="compact">
          <v-list-item v-for="shop in parsedDashboardData.shop_summaries" :key="shop.id">
            <v-list-item-title>{{ shop.name }}</v-list-item-title>
            <v-list-item-subtitle>今日の予約: {{ shop.today_bookings }}件 / ステータス: {{ shop.status }}</v-list-item-subtitle>
            <template v-slot:append>
              <v-btn variant="text" size="small" :href="`/owner/shops/${shop.id}`">詳細</v-btn>
            </template>
          </v-list-item>
        </v-list>

        <v-divider class="my-4"></v-divider>

        <h3 class="text-h6 mb-2">最近のアクティビティ</h3>
        <v-list density="compact">
          <v-list-item v-for="(activity, i) in parsedDashboardData.recent_activities" :key="i">
            <v-list-item-title>{{ activity.description }}</v-list-item-title>
            <v-list-item-subtitle>{{ activity.time }}</v-list-item-subtitle>
          </v-list-item>
        </v-list>

        <v-divider class="my-4"></v-divider>

        <h3 class="text-h6 mb-2">クイックリンク</h3>
        <v-row>
          <v-col cols="6">
            <v-btn :href="`/owner/shops`" block>店舗一覧</v-btn>
          </v-col>
          <v-col cols="6">
            <v-btn :href="`/owner/contracts`" block>契約管理</v-btn>
          </v-col>
        </v-row>

      </v-card-text>
    </v-card>
  </v-container>
  <v-container v-else>
    <p class="text-center">ダッシュボード情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

interface DashboardData {
  total_shops: number;
  active_contracts: number;
  pending_bookings_across_shops: number;
  recent_activities: Array<{ type: string; description: string; time: string }>;
  shop_summaries: Array<{ id: number; name: string; today_bookings: number; status: string }>;
}

const props = defineProps<{
  dashboardData: string; // JSON文字列として受け取る
}>();

const parsedDashboardData = ref<DashboardData | null>(null);

onMounted(() => {
  try {
    parsedDashboardData.value = JSON.parse(props.dashboardData);
  } catch (e) {
    console.error("Failed to parse dashboard data:", e);
    parsedDashboardData.value = null;
  }
});
</script>
