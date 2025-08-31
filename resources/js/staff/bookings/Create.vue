<template>
  <v-container>
    <form :action="action" method="POST">
      <!-- CSRFトークン -->
      <input type="hidden" name="_token" :value="csrfToken">

      <!-- 送信する予約データ -->
      <input type="hidden" name="date" :value="form.date?.toISOString().split('T')[0]">
      <input type="hidden" name="staff_id" :value="form.staff?.id">
      <input type="hidden" name="staff_name" :value="form.staff?.name">
      <input type="hidden" name="service_id" :value="form.service?.id">
      <input type="hidden" name="service_name" :value="form.service?.name">
      <input type="hidden" name="time" :value="form.time">

      <!-- 予約者情報 -->
      <input type="hidden" name="booker_name" :value="form.booker_name">
      <input type="hidden" name="booker_email" :value="form.booker_email">
      <input type="hidden" name="booker_tel" :value="form.booker_tel">

      <!-- 予約者情報入力方法選択 (初期画面) -->
      <v-card v-if="!inputMethod" class="mx-auto" max-width="600">
        <v-card-title class="text-h6 font-weight-bold">予約者情報の入力方法を選択</v-card-title>
        <v-divider></v-divider>
        <v-card-text class="text-center">
          <v-btn color="primary" class="mb-4" block @click="inputMethod = 'from_history'">
            過去の予約から予約者を取得（今後実装予定）
          </v-btn>
          <v-btn color="secondary" block @click="inputMethod = 'later'">
            予約者情報は後で入力
          </v-btn>
        </v-card-text>
      </v-card>

      <!-- 過去の予約から取得 (今後実装予定) -->
      <v-card v-if="inputMethod === 'from_history'" class="mx-auto" max-width="600">
        <v-card-title class="text-h6 font-weight-bold">過去の予約から取得</v-card-title>
        <v-divider></v-divider>
        <v-card-text class="text-center">
          <p class="mb-4">この機能は今後実装予定です。</p>
          <v-btn @click="inputMethod = null">戻る</v-btn>
        </v-card-text>
      </v-card>

      <!-- 予約作成フォーム本体 (inputMethod が 'later' の場合のみ表示) -->
      <div v-if="inputMethod === 'later'">
        <!-- Step 1: 日付選択 -->
        <v-card v-if="step === 1" class="mx-auto" max-width="600">
          <v-card-title class="text-h6 font-weight-bold">1. 来店日を選択</v-card-title>
          <v-divider></v-divider>
          <v-card-text class="d-flex justify-center">
            <v-date-picker
              :model-value="form.date"
              :allowed-dates="allowedDates"
              @update:model-value="handleDateChange"
            ></v-date-picker>
          </v-card-text>
        </v-card>

        <!-- Step 2: 詳細入力 -->
        <v-card v-if="step === 2" class="mx-auto" max-width="600">
          <v-card-title class="text-h6 font-weight-bold">2. 担当者・サービス・時間を選択</v-card-title>
          <v-divider></v-divider>
          <v-card-text>
            <v-select
              v-model="form.staff"
              :items="staffOptions"
              item-title="name"
              item-value="id"
              label="担当者"
              return-object
              class="mb-4"
              :loading="loading.staff"
              @update:model-value="fetchAvailability"
            ></v-select>

            <v-select
              v-model="form.service"
              :items="serviceOptions"
              item-title="name"
              item-value="id"
              label="サービス"
              return-object
              :loading="loading.service"
              @update:model-value="fetchAvailability"
            ></v-select>

            <div v-if="form.staff && form.service" class="mt-4">
                <p class="font-weight-medium mb-2">ご希望の時間を選択</p>
                <p v-if="loading.availability">空き時間を検索中...</p>
                <v-chip-group v-else v-model="form.time" mandatory filter selected-class="text-primary">
                <v-chip
                    v-for="timeSlot in availableTimes"
                    :key="timeSlot"
                    :value="timeSlot"
                    >{{ timeSlot }}</v-chip
                >
                </v-chip-group>
                <p v-if="!loading.availability && availableTimes.length === 0" class="text-grey">選択可能な時間がありません。</p>
            </div>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-btn @click="step--">戻る</v-btn>
            <v-spacer></v-spacer>
            <v-btn color="primary" @click="step++" :disabled="isStepDisabled">次へ</v-btn>
          </v-card-actions>
        </v-card>

        <!-- Step 3: 予約者情報入力 -->
        <v-card v-if="step === 3" class="mx-auto" max-width="600">
          <v-card-title class="text-h6 font-weight-bold">3. 予約者情報を入力</v-card-title>
          <v-divider></v-divider>
          <v-card-text>
            <v-text-field v-model="form.booker_name" label="予約者名" required></v-text-field>
            <v-text-field v-model="form.booker_email" label="メールアドレス" type="email"></v-text-field>
            <v-text-field v-model="form.booker_tel" label="電話番号"></v-text-field>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-btn @click="step--">戻る</v-btn>
            <v-spacer></v-spacer>
            <v-btn color="primary" @click="step++" :disabled="!form.booker_name">次へ</v-btn>
          </v-card-actions>
        </v-card>

        <!-- Step 4: 内容確認 -->
        <v-card v-if="step === 4" class="mx-auto" max-width="600">
            <v-card-title class="text-h6 font-weight-bold">4. 予約内容の確認</v-card-title>
            <v-divider></v-divider>
            <v-list lines="one">
                <v-list-item title="日付" :subtitle="formattedDate"></v-list-item>
                <v-list-item title="時間" :subtitle="form.time"></v-list-item>
                <v-list-item title="担当者" :subtitle="form.staff?.name"></v-list-item>
                <v-list-item title="サービス" :subtitle="form.service?.name"></v-list-item>
                <v-list-item title="予約者名" :subtitle="form.booker_name"></v-list-item>
                <v-list-item title="メールアドレス" :subtitle="form.booker_email"></v-list-item>
                <v-list-item title="電話番号" :subtitle="form.booker_tel"></v-list-item>
            </v-list>
            <v-divider></v-divider>
            <v-card-actions>
                <v-btn @click="step--">戻る</v-btn>
                <v-spacer></v-spacer>
                <v-btn type="submit" color="primary">予約を確定する</v-btn>
            </v-card-actions>
        </v-card>
      </div>
    </form>
  </v-container>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios'; // ダミーAPI呼び出し用

// Props
const props = defineProps<{ 
    shopId: string;
    action: string; // フォームのPOST先URL
}>();

// CSRFトークンを格納するref
const csrfToken = ref('');

// State
const step = ref(1);
const form = reactive({
  date: null as Date | null,
  staff: null as { id: number; name: string } | null,
  service: null as { id: number; name: string, duration: number } | null,
  time: null as string | null,
  booker_name: '' as string,
  booker_email: '' as string,
  booker_tel: '' as string,
});

const staffOptions = ref<{ id: number; name: string }[]>([]);
const serviceOptions = ref<{ id: number; name: string, duration: number }[]>([]);
const availableTimes = ref<string[]>([]);

const loading = reactive({
    staff: false,
    service: false,
    availability: false,
});

// 新しい状態変数
const inputMethod = ref<string | null>(null); // 'from_history', 'later'

// Computed
const formattedDate = computed(() => {
    if (!form.date) return '';
    return form.date.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric' });
});

const isStepDisabled = computed(() => {
    if (inputMethod.value !== 'later') return true; // 新しいボタン表示中は無効
    if (step.value === 1 && !form.date) return true;
    if (step.value === 2 && (!form.staff || !form.service || !form.time)) return true;
    if (step.value === 3 && !form.booker_name) return true; // 予約者名必須
    return false;
});

// Methods
const allowedDates = (date: Date) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return date >= today;
};

const handleDateChange = (newDate: Date | null) => {
    form.date = newDate;
    if (!form.date) return;

    form.staff = null;
    form.service = null;
    form.time = null;
    availableTimes.value = [];

    fetchStaff();
    fetchServices();
    step.value = 2;
};

// --- API通信 (ダミー実装) ---
const fetchStaff = async () => {
  if (!form.date) return;
  loading.staff = true;
  console.log(`Fetching staff for shop ${props.shopId} on ${form.date.toISOString().split('T')[0]}`);
  await new Promise(resolve => setTimeout(resolve, 500));
  staffOptions.value = [
    { id: 1, name: '山田 太郎' },
    { id: 2, name: '鈴木 花子' },
  ];
  loading.staff = false;
};

const fetchServices = async () => {
  loading.service = true;
  console.log(`Fetching services for shop ${props.shopId}`);
  await new Promise(resolve => setTimeout(resolve, 500));
  serviceOptions.value = [
    { id: 1, name: 'カット (60分)', duration: 60 },
    { id: 2, name: 'カラー (90分)', duration: 90 },
    { id: 3, name: 'パーマ (120分)', duration: 120 },
  ];
  loading.service = false;
};

const fetchAvailability = async () => {
  if (!form.date || !form.staff || !form.service) return;
  loading.availability = true;
  availableTimes.value = [];
  console.log(`Fetching availability for...`, form);
  await new Promise(resolve => setTimeout(resolve, 800));
  availableTimes.value = ['10:00', '10:30', '11:00', '14:00', '14:30', '15:00', '15:30'];
  loading.availability = false;
};

// コンポーネントがマウントされた時にmetaタグからCSRFトークンを取得
onMounted(() => {
  const tokenElement = document.querySelector('meta[name="csrf-token"]');
  if (tokenElement) {
    csrfToken.value = tokenElement.getAttribute('content') || '';
  }
});

</script>