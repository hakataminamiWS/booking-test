<template>
    <v-container>
        <form :action="action" method="POST">
            <!-- CSRFトークン -->
            <input type="hidden" name="_token" :value="csrfToken" />

            <!-- 送信する予約データ -->
            <input
                type="hidden"
                name="date"
                :value="form.date?.toISOString().split('T')[0]"
            />
            <input type="hidden" name="staff_id" :value="form.staff?.id" />
            <input type="hidden" name="staff_name" :value="form.staff?.name" />
            <input type="hidden" name="service_id" :value="form.service?.id" />
            <input
                type="hidden"
                name="service_name"
                :value="form.service?.name"
            />
            <input type="hidden" name="time" :value="form.time" />

            <!-- Step 1: 日付選択 -->
            <v-card v-if="step === 1" class="mx-auto" max-width="600">
                <v-card-title class="text-h6 font-weight-bold"
                    >1. ご希望の来店日を選択</v-card-title
                >
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
                <v-card-title class="text-h6 font-weight-bold"
                    >2. 詳細を入力</v-card-title
                >
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
                        <p class="font-weight-medium mb-2">
                            ご希望の時間を選択
                        </p>
                        <p v-if="loading.availability">空き時間を検索中...</p>
                        <v-chip-group
                            v-else
                            v-model="form.time"
                            mandatory
                            filter
                            selected-class="text-primary"
                        >
                            <v-chip
                                v-for="timeSlot in availableTimes"
                                :key="timeSlot"
                                :value="timeSlot"
                                >{{ timeSlot }}</v-chip
                            >
                        </v-chip-group>
                        <p
                            v-if="
                                !loading.availability &&
                                availableTimes.length === 0
                            "
                            class="text-grey"
                        >
                            選択可能な時間がありません。
                        </p>
                    </div>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-btn @click="step--">戻る</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="primary"
                        @click="step++"
                        :disabled="isNextDisabled"
                        >次へ</v-btn
                    >
                </v-card-actions>
            </v-card>

            <!-- Step 3: 内容確認 -->
            <v-card v-if="step === 3" class="mx-auto" max-width="600">
                <v-card-title class="text-h6 font-weight-bold"
                    >3. 予約内容の確認</v-card-title
                >
                <v-divider></v-divider>
                <v-list lines="one">
                    <v-list-item
                        title="日付"
                        :subtitle="formattedDate"
                    ></v-list-item>
                    <v-list-item
                        title="担当者"
                        :subtitle="form.staff?.name"
                    ></v-list-item>
                    <v-list-item
                        title="サービス"
                        :subtitle="form.service?.name"
                    ></v-list-item>
                    <v-list-item
                        title="時間"
                        :subtitle="form.time"
                    ></v-list-item>
                </v-list>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-btn @click="step--">戻る</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn type="submit" color="primary">予約を確定する</v-btn>
                </v-card-actions>
            </v-card>
        </form>
    </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useBookingForm } from "../composables/useBookingForm";

// Props
const props = defineProps<{
    shopId: string;
    action: string;
}>();

// CSRFトークンを格納するref
const csrfToken = ref("");

// ロジックをコンポーザブルから注入
const {
    step,
    form,
    staffOptions,
    serviceOptions,
    availableTimes,
    loading,
    formattedDate,
    isNextDisabled,
    allowedDates,
    handleDateChange,
    fetchAvailability,
} = useBookingForm(props);

// コンポーネントがマウントされた時にmetaタグからCSRFトークンを取得
onMounted(() => {
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    if (tokenElement) {
        csrfToken.value = tokenElement.getAttribute("content") || "";
    }
});
</script>
