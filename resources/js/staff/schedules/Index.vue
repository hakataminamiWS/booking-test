<template>
  <v-container v-if="parsedScheduleData">
    <form :action="action" method="POST">
      <input type="hidden" name="_token" :value="csrfToken">
      <input type="hidden" name="week_start_date" :value="parsedScheduleData.week_start_date">

      <v-card class="mx-auto" max-width="1000">
        <v-card-title class="text-h5 font-weight-bold">
          予約可能枠管理 (店舗ID: {{ shopId }})
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <h3 class="text-h6 mb-4">
            {{ parsedScheduleData.week_start_date }} 〜 {{ parsedScheduleData.week_end_date }} のスケジュール
          </h3>

          <v-row class="mb-4">
            <v-col cols="auto">
              <v-btn @click="changeWeek(-1)">前週</v-btn>
            </v-col>
            <v-col cols="auto">
              <v-btn @click="changeWeek(1)">次週</v-btn>
            </v-col>
            <v-col cols="auto">
              <v-btn @click="copyPreviousWeekSchedule" color="info">先週のスケジュールをコピー</v-btn>
            </v-col>
          </v-row>

          <v-alert type="info" variant="tonal" class="mb-4">
            <p class="font-weight-bold">検討事項:</p>
            <p>「店舗の店休日かどうかは、オーナーが入力する店休日が反映されるイメージ」</p>
            <p class="text-caption">（オーナーが設定した店休日は、この画面でスタッフが予約を受け付けられないように表示・制御されます。現在はダミーデータです。）</p>
            <p class="font-weight-bold mt-2">予約受付可能時間・終了時間について:</p>
            <p class="text-caption">予約受付可能時間、予約受付終了時間は、その日の予約の最終受付時間を示します。</p>
          </v-alert>

          <v-row v-for="(day, index) in parsedScheduleData.days" :key="day.date" class="mb-4 align-center">
            <v-col cols="2">
              <div class="font-weight-bold">{{ day.day_of_week }}</div>
              <div>{{ day.date }}</div>
            </v-col>
            <v-col cols="2">
              <v-switch
                v-model="day.is_working_day"
                label="出勤日"
                hide-details
              ></v-switch>
            </v-col>
            <v-col cols="6" v-if="day.is_working_day">
              <v-row>
                <v-col cols="6">
                  <v-text-field
                    v-model="day.open_time"
                    label="受付開始"
                    type="time"
                    hide-details
                  ></v-text-field>
                </v-col>
                <v-col cols="6">
                  <v-text-field
                    v-model="day.close_time"
                    label="受付終了"
                    type="time"
                    hide-details
                  ></v-text-field>
                </v-col>
                <v-col cols="6">
                  <v-text-field
                    v-model="day.break_start"
                    label="休憩開始"
                    type="time"
                    hide-details
                  ></v-text-field>
                </v-col>
                <v-col cols="6">
                  <v-text-field
                    v-model="day.break_end"
                    label="休憩終了"
                    type="time"
                    hide-details
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-col>
            <v-col cols="6" v-else>
              <v-chip color="grey">休日</v-chip>
              <v-chip v-if="day.is_shop_holiday" color="red" class="ml-2">店舗定休日</v-chip>
            </v-col>
            <!-- hidden input for each day's data -->
            <input type="hidden" :name="`days[${index}][date]`" :value="day.date">
            <input type="hidden" :name="`days[${index}][is_working_day]`" :value="day.is_working_day ? '1' : '0'">
            <input type="hidden" :name="`days[${index}][open_time]`" :value="day.open_time || ''">
            <input type="hidden" :name="`days[${index}][close_time]`" :value="day.close_time || ''">
            <input type="hidden" :name="`days[${index}][break_start]`" :value="day.break_start || ''">
            <input type="hidden" :name="`days[${index}][break_end]`" :value="day.break_end || ''">
          </v-row>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn type="submit" color="primary">スケジュールを保存</v-btn>
        </v-card-actions>
      </v-card>
    </form>
  </v-container>
  <v-container v-else>
    <p class="text-center">スケジュール情報が見つかりませんでした。</p>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { format, addDays, subDays } from 'date-fns'; // subDays をインポート

interface DaySchedule {
  date: string;
  day_of_week: string;
  is_working_day: boolean;
  open_time: string | null;
  close_time: string | null;
  break_start: string | null;
  break_end: string | null;
  is_shop_holiday: boolean;
}

interface ScheduleData {
  week_start_date: string;
  week_end_date: string;
  days: DaySchedule[];
  general_settings: {
    default_open_time: string;
    default_close_time: string;
    default_break_start: string;
    default_break_end: string;
  };
  considerations: {
    shop_holiday_reflection: string;
    flexible_booking_slots: string;
  };
}

const props = defineProps<{
  shopId: string;
  scheduleData: string; // JSON文字列として受け取る
  action: string; // フォームのPOST先URL
}>();

const parsedScheduleData = ref<ScheduleData | null>(null);
const csrfToken = ref('');

// 週の切り替え（ダミー機能）
const changeWeek = (offset: number) => {
  if (!parsedScheduleData.value) return;
  const currentStartDate = new Date(parsedScheduleData.value.week_start_date);
  const newStartDate = addDays(currentStartDate, offset * 7);
  window.location.href = `/shops/${props.shopId}/staff/schedules?week_start_date=${format(newStartDate, 'yyyy-MM-dd')}`;
};

// 先週のスケジュールをコピーする機能
const copyPreviousWeekSchedule = () => {
  if (!parsedScheduleData.value) return;

  // 現在の週のデータをコピーし、日付だけを前週にずらす
  const currentDays = parsedScheduleData.value.days;
  const newDays = currentDays.map(day => {
    const originalDate = new Date(day.date);
    const previousWeekDate = subDays(originalDate, 7); // 7日前
    return {
      ...day,
      date: format(previousWeekDate, 'yyyy-MM-dd'),
      // is_shop_holiday はオーナー設定なのでコピーしない（ダミー）
      is_shop_holiday: false, // ダミーでリセット
    };
  });

  // parsedScheduleData を更新
  parsedScheduleData.value.days = newDays;

  // 週の開始日と終了日も更新（UI表示用）
  const currentWeekStartDate = new Date(parsedScheduleData.value.week_start_date);
  const previousWeekStartDate = subDays(currentWeekStartDate, 7);
  parsedScheduleData.value.week_start_date = format(previousWeekStartDate, 'yyyy-MM-dd');
  parsedScheduleData.value.week_end_date = format(addDays(previousWeekStartDate, 6), 'yyyy-MM-dd');

  alert('先週のスケジュールをコピーしました。日付を確認し、保存してください。');
};

onMounted(() => {
  const tokenElement = document.querySelector('meta[name="csrf-token"]');
  if (tokenElement) {
    csrfToken.value = tokenElement.getAttribute('content') || '';
  }

  try {
    parsedScheduleData.value = JSON.parse(props.scheduleData);
  } catch (e) {
    console.error("Failed to parse schedule data:", e);
    parsedScheduleData.value = null;
  }
});
</script>