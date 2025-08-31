<template>
  <v-container v-if="parsedDashboardData">
    <v-card class="mx-auto" max-width="800">
      <v-card-title class="text-h5 font-weight-bold">スタッフダッシュボード (店舗ID: {{ shopId }})</v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <!-- 今日の予約サマリー -->
        <h3 class="text-h6 mb-2">今日の予約サマリー</h3>
        <p class="text-caption text-grey">（検討事項: 確定済み、ペンディング、キャンセル済みなどステータス別の件数、今日の売上合計など）</p>
        <v-row>
          <v-col cols="4">
            <v-card variant="outlined">
              <v-card-text class="text-center">
                <div class="text-h5">{{ parsedDashboardData.today_summary.confirmed_bookings }}</div>
                <div class="text-subtitle-1">確定済み</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="4">
            <v-card variant="outlined">
              <v-card-text class="text-center">
                <div class="text-h5">{{ parsedDashboardData.today_summary.pending_bookings }}</div>
                <div class="text-subtitle-1">ペンディング</div>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="4">
            <v-card variant="outlined">
              <v-card-text class="text-center">
                <div class="text-h5">{{ parsedDashboardData.today_summary.total_revenue }}</div>
                <div class="text-subtitle-1">今日の売上（ダミー）</div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <v-divider class="my-4"></v-divider>

        <!-- 今後の予約リスト -->
        <h3 class="text-h6 mb-2">今日の来店予定</h3>
        <p class="text-caption text-grey">（検討事項: 時間順のリスト、予約者名、サービス、ステータスなど）</p>
        <v-list density="compact">
          <v-list-item v-for="(booking, i) in parsedDashboardData.upcoming_bookings" :key="i">
            <v-list-item-title>{{ booking.time }} - {{ booking.booker_name }} ({{ booking.service }})</v-list-item-title>
            <v-list-item-subtitle>{{ booking.status }}</v-list-item-subtitle>
          </v-list-item>
          <v-list-item v-if="parsedDashboardData.upcoming_bookings.length === 0">
            <v-list-item-title>今日の来店予定はありません。</v-list-item-title>
          </v-list-item>
        </v-list>

        <v-divider class="my-4"></v-divider>

        <!-- 未対応のタスク -->
        <h3 class="text-h6 mb-2">未対応のタスク</h3>
        <p class="text-caption text-grey">（検討事項: 新規予約リクエスト、キャンセルリクエスト、変更リクエストなど、スタッフが対応すべき事項の件数）</p>
        <v-list density="compact">
          <v-list-item>
            <v-list-item-title>新規予約リクエスト: {{ parsedDashboardData.pending_tasks.new_booking_requests }}件</v-list-item-title>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>キャンセルリクエスト: {{ parsedDashboardData.pending_tasks.cancellation_requests }}件</v-list-item-title>
          </v-list-item>
        </v-list>

        <v-divider class="my-4"></v-divider>

        <!-- クイックリンク -->
        <h3 class="text-h6 mb-2">クイックリンク</h3>
        <p class="text-caption text-grey">（検討事項: 予約一覧、新規予約追加、スケジュール管理、顧客管理など、よく使う機能へのリンク）</p>
        <v-row>
          <v-col v-for="(link, i) in parsedDashboardData.quick_links" :key="i" cols="6">
            <v-btn :href="link.url" block>{{ link.text }}</v-btn>
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
  today_summary: {
    confirmed_bookings: number;
    pending_bookings: number;
    canceled_bookings: number;
    total_revenue: string;
  };
  upcoming_bookings: Array<{
    time: string;
    booker_name: string;
    service: string;
    status: string;
  }>;
  pending_tasks: {
    new_booking_requests: number;
    cancellation_requests: number;
    change_requests: number;
  };
  quick_links: Array<{
    text: string;
    url: string;
  }>;
}

const props = defineProps<{
  shopId: string;
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
