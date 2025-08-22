<template>
  <v-container fluid>
    <h1>予約フォーム</h1>
    <p class="mb-4">ご希望の日付、担当者、サービス、時間を選択してください。</p>

    <form :action="action" method="POST">
      <input type="hidden" name="_token" :value="csrfToken">

      <v-row>
        <!-- 1. 日付選択 (カレンダー) -->
        <v-col cols="12" md="4">
          <div v-if="display.mdAndUp.value || step === 'date'">
            <v-date-picker
              v-model="selectedDate"
              title="日付を選択"
            ></v-date-picker>
          </div>
        </v-col>

        <!-- 2. 担当者・サービス・時間選択 -->
        <v-col cols="12" md="8">
          <div v-if="selectedDate && (display.mdAndUp.value || step === 'details')">
            <!-- SP用：戻るボタン -->
            <v-btn
              v-if="display.smAndDown.value"
              @click="goBackToCalendar"
              class="mb-4"
              prepend-icon="mdi-arrow-left"
            >
              日付選択に戻る
            </v-btn>

            <!-- 担当者選択 -->
            <div>
              <h2>担当者を選択</h2>
              <div v-if="loading.staff">
                <v-progress-circular indeterminate></v-progress-circular>
                <span>担当者を取得中...</span>
              </div>
              <v-select
                v-else
                v-model="selectedStaff"
                :items="staffList"
                item-title="name"
                item-value="id"
                label="担当者"
                return-object
                :disabled="!staffList.length"
              ></v-select>
            </div>

            <!-- サービス選択 -->
            <div v-if="selectedStaff" class="mt-4">
              <h2>サービスを選択</h2>
              <div v-if="loading.services">
                <v-progress-circular indeterminate></v-progress-circular>
                <span>サービスを取得中...</span>
              </div>
              <v-select
                v-else
                v-model="selectedService"
                :items="serviceList"
                item-title="name"
                item-value="id"
                label="サービス"
                return-object
                :disabled="!serviceList.length"
              ></v-select>
            </div>

            <!-- 時間選択 -->
            <div v-if="selectedService" class="mt-4">
              <h2>時間を選択</h2>
              <div v-if="loading.slots">
                <v-progress-circular indeterminate></v-progress-circular>
                <span>空き時間を取得中...</span>
              </div>
              <div v-else-if="Object.keys(groupedTimeSlots).length > 0">
                <div v-for="(slotsInHour, hour) in groupedTimeSlots" :key="hour" class="mb-3">
                  <p class="text-subtitle-1 font-weight-bold">{{ hour }}:00</p>
                  <v-chip-group v-model="selectedTime" column>
                    <v-chip
                      v-for="slot in slotsInHour"
                      :key="slot.time"
                      :value="slot.time"
                      filter
                      variant="outlined"
                    >
                      {{ slot.time }}
                    </v-chip>
                  </v-chip-group>
                </div>
              </div>
              <p v-else>選択可能な時間がありません。</p>
            </div>

            <!-- 予約ボタン -->
            <v-btn v-if="isSubmittable" type="submit" color="primary" class="mt-6" :disabled="!isSubmittable" block>
              予約を確定する
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- 送信データ -->
      <input type="hidden" name="date" :value="formattedDate">
      <input type="hidden" name="staff_id" :value="selectedStaff?.id">
      <input type="hidden" name="service_id" :value="selectedService?.id">
      <input type="hidden" name="time" :value="selectedTime">

    </form>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useDisplay } from 'vuetify';

// --- 型定義 ---
interface Staff { id: number; name: string; }
interface Service { id: number; name: string; duration: number; }
interface TimeSlot { time: string; available: boolean; }

// --- Props ---
const props = defineProps({
  action: { type: String, required: true },
  apiAvailability: { type: String, required: true }
});

// --- Vuetify Display ---
const display = useDisplay();

// --- リアクティブな状態 ---
const step = ref<'date' | 'details'>('date');
const csrfToken = ref('');
const selectedDate = ref<Date | null>(null);
const selectedStaff = ref<Staff | null>(null);
const selectedService = ref<Service | null>(null);
const selectedTime = ref<string | null>(null);
const staffList = ref<Staff[]>([]);
const serviceList = ref<Service[]>([]);
const timeSlots = ref<TimeSlot[]>([]);
const loading = ref({ staff: false, services: false, slots: false });

// --- コンピュートプロパティ ---
const formattedDate = computed(() => {
  if (!selectedDate.value) return '';
  const d = selectedDate.value;
  return `${d.getFullYear()}-${('0' + (d.getMonth() + 1)).slice(-2)}-${('0' + d.getDate()).slice(-2)}`;
});

const isSubmittable = computed(() => {
  return !!(selectedDate.value && selectedStaff.value && selectedService.value && selectedTime.value);
});

const groupedTimeSlots = computed(() => {
  // 1. 利用可能なスロットをフィルタリング
  const availableSlots = timeSlots.value.filter(slot => slot.available);

  // 2. 時間ごとにグループ化
  return availableSlots.reduce((acc, slot) => {
    const hour = slot.time.substring(0, 2);
    if (!acc[hour]) {
      acc[hour] = [];
    }
    acc[hour].push(slot);
    return acc;
  }, {} as Record<string, TimeSlot[]>);
});

// --- ライフサイクル ---
onMounted(() => {
  csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
});

// --- メソッド ---
const goBackToCalendar = () => {
  step.value = 'date';
};

// --- ウォッチャー ---
watch(selectedDate, async (newDate, oldDate) => {
  if (newDate && display.smAndDown.value) {
    step.value = 'details';
  }
  if (!newDate) {
    selectedStaff.value = null;
    staffList.value = [];
    return;
  }
  if (oldDate && newDate.getTime() === oldDate.getTime()) {
    return;
  }

  selectedStaff.value = null;
  staffList.value = [];
  loading.value.staff = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 500));
    staffList.value = [
      { id: 1, name: '山田 太郎' },
      { id: 2, name: '鈴木 花子' },
    ];
  } catch (error) {
    console.error('担当者リストの取得に失敗しました:', error);
  } finally {
    loading.value.staff = false;
  }
});

watch(selectedStaff, async (newStaff) => {
  selectedService.value = null;
  serviceList.value = [];
  if (!newStaff) return;

  loading.value.services = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 500));
    serviceList.value = [
      { id: 1, name: 'カット', duration: 60 },
      { id: 2, name: 'カラー', duration: 90 },
      { id: 3, name: 'パーマ', duration: 120 },
    ];
  } catch (error) {
    console.error('サービスリストの取得に失敗しました:', error);
  } finally {
    loading.value.services = false;
  }
});

watch(selectedService, async (newService) => {
  selectedTime.value = null;
  timeSlots.value = [];
  if (!newService || !selectedDate.value || !selectedStaff.value) return;

  loading.value.slots = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 500));
    
    const slots: TimeSlot[] = [];
    const startTime = 9 * 60, endTime = 17 * 60, interval = 15;
    for (let minutes = startTime; minutes < endTime; minutes += interval) {
      const hour = Math.floor(minutes / 60);
      const minute = minutes % 60;
      const time = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
      const available = Math.random() > 0.3;
      slots.push({ time, available });
    }
    timeSlots.value = slots;
  } catch (error) {
    console.error('時間枠の取得に失敗しました:', error);
  } finally {
    loading.value.slots = false;
  }
});
</script>

<style scoped>
/* 必要に応じてスタイルを追加 */
</style>
