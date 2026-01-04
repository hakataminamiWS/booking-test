<template>
    <v-app>
        <v-main>
            <v-container class="container-width-600 px-4">
                <!-- ナビゲーション -->
                <v-row no-gutters>
                    <v-col cols="12">
                        <v-btn :href="`/shops/${props.shop.slug}/booker/bookings`" prepend-icon="mdi-arrow-left"
                               variant="text" class="px-0">
                            予約履歴に戻る
                        </v-btn>
                    </v-col>
                </v-row>

                <!-- 店舗ヘッダー -->
                <v-row>
                    <v-col cols="12">
                        <ShopHeader :shop="shop" />
                    </v-col>
                </v-row>

                <!-- メインフォーム -->
                <form id="booking-create-form" :action="`/shops/${props.shop.slug}/booker/bookings`" method="POST">
                    <input type="hidden" name="_token" :value="props.csrfToken" />
                    <input type="hidden" name="start_at" :value="form.start_at" />

                    <!-- バリデーションエラー -->
                    <v-alert v-if="props.errors.length > 0" type="error">
                        <ul>
                            <li v-for="(error, i) in props.errors" :key="i">
                                {{ error }}
                            </li>
                        </ul>
                    </v-alert>

                    <v-row>
                        <!-- メニュー＆オプション -->
                        <v-col cols="12">
                            <v-card variant="text">
                                <v-card-title class="px-0">メニュー選択</v-card-title>
                                <v-card-text class="px-0">
                                    <v-select v-model="form.menu_id"
                                              hide-details
                                              name="menu_id"
                                              :items="props.menus"
                                              item-title="name"
                                              item-value="id" label="メニュー（必須）" required class="mb-2">
                                    </v-select>
                                    <v-select v-model="form.option_ids"
                                              hide-details
                                              :items="availableOptions"
                                              item-title="name"
                                              item-value="id"
                                              label="オプション" multiple chips
                                              closable-chips
                                              :disabled="!form.menu_id">
                                    </v-select>
                                    <input v-for="optId in form.option_ids" :key="optId" type="hidden"
                                           name="option_ids[]"
                                           :value="optId" />

                                    <div v-if="form.menu_id" class="mt-4 text-subtitle-1 text-right">
                                        目安: {{ totalDuration }}分 / {{ totalPrice.toLocaleString() }}円
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-col>

                        <!-- スタッフ指名 -->
                        <v-col cols="12">
                            <v-card variant="text">
                                <v-card-title class="px-0">スタッフ指名</v-card-title>
                                <v-card-text class="px-0">
                                    <v-select v-model="form.assigned_staff_id"
                                              hide-details
                                              name="assigned_staff_id"
                                              :items="availableStaffs"
                                              item-title="profile.nickname"
                                              item-value="id"
                                              label="担当スタッフ（必須）"
                                              :disabled="!form.menu_id">
                                        <template v-slot:item="{ item, props }">
                                            <v-list-item v-bind="props" :title="item.raw.profile?.nickname">
                                                <template v-slot:prepend>
                                                    <v-avatar size="40">
                                                        <v-img v-if="item.raw.profile?.small_image_url"
                                                               :src="item.raw.profile?.small_image_url" />
                                                        <v-icon v-else>mdi-account</v-icon>
                                                    </v-avatar>
                                                </template>
                                            </v-list-item>
                                        </template>
                                        <template v-slot:selection="{ item }">
                                            <v-avatar size="32" class="mr-2">
                                                <v-img v-if="item.raw.profile?.small_image_url"
                                                       :src="item.raw.profile?.small_image_url" />
                                                <v-icon v-else size="small">mdi-account</v-icon>
                                            </v-avatar>
                                            {{ item.raw.profile?.nickname }}
                                        </template>
                                    </v-select>
                                    <v-alert v-if="staffWarning" type="warning" density="compact" variant="tonal"
                                             class="mt-2">
                                        {{ staffWarning }}
                                    </v-alert>
                                </v-card-text>
                            </v-card>
                        </v-col>

                        <!-- 日時選択 -->
                        <v-col cols="12">
                            <v-card variant="text">
                                <v-card-title class="px-0">
                                    日時選択
                                </v-card-title>

                                <v-card-text class="px-0" v-if="!form.menu_id">
                                    <p>
                                        メニューを選択してください
                                    </p>
                                </v-card-text>

                                <v-card-text class="px-0" v-else-if="!form.assigned_staff_id">
                                    <p>
                                        担当スタッフを選択してください
                                    </p>
                                </v-card-text>

                                <v-card-text class="pa-0" v-else>
                                    <v-card variant="text">
                                        <v-card-title class="px-0 text-body-1">
                                            日付を選択
                                        </v-card-title>

                                        <v-card-text class="px-0">
                                            <v-date-picker v-model="selectedDateValue" hide-header
                                                           @update:year="onPickerYearChange"
                                                           @update:month="onPickerMonthChange"
                                                           :allowed-dates="allowedDates" show-adjacent-months
                                                           class="w-100">
                                            </v-date-picker>
                                        </v-card-text>
                                    </v-card>

                                    <v-card variant="text">
                                        <v-card-title class="px-0 text-body-1">
                                            時間を選択
                                        </v-card-title>

                                        <v-card-text class="px-0" v-if="groupedTimeSlots.length > 0">

                                            <div v-for="group in groupedTimeSlots" :key="group.hour"
                                                 class="d-flex align-center py-1">
                                                <div class="text-body-2 font-weight-bold mr-4" style="width: 40px">
                                                    {{ group.hour }}時
                                                </div>
                                                <v-chip-group v-model="selectedTime" column mandatory
                                                              active-class="primary">
                                                    <v-chip v-for="time in group.slots" :key="time" :value="time"
                                                            variant="outlined"
                                                            size="default" class="px-3">
                                                        <v-icon v-if="selectedTime === time" start
                                                                size="small">mdi-check</v-icon>
                                                        {{ time }}
                                                    </v-chip>
                                                </v-chip-group>
                                            </div>
                                        </v-card-text>

                                        <v-card-text class="px-0" v-else>
                                            予約可能な時間帯がありません。
                                        </v-card-text>

                                        <v-card-text class="pa-0">
                                            <v-alert v-if="shiftWarning" type="warning" density="compact"
                                                     variant="tonal" class="mt-2">
                                                {{ shiftWarning }}
                                            </v-alert>

                                            <v-alert v-if="conflictWarning" type="error" density="compact"
                                                     variant="tonal" class="mt-2">
                                                {{ conflictWarning }}
                                            </v-alert>

                                            <p v-if="displayDateTime" class="text-subtitle-1">
                                                予約日時: {{ displayDateTime }}
                                            </p>
                                        </v-card-text>
                                    </v-card>
                                </v-card-text>
                            </v-card>
                        </v-col>

                        <!-- 予約メモ -->
                        <v-col cols="12">
                            <v-card variant="text">
                                <v-card-title class="px-0">
                                    予約メモ
                                </v-card-title>
                                <v-card-text class="px-0">
                                    <v-textarea v-model="form.note_from_booker" name="note_from_booker"
                                                label="店舗への連絡事項（任意）" rows="3"
                                                placeholder="ご要望などがございましたらご記入ください"></v-textarea>
                                </v-card-text>
                            </v-card>
                        </v-col>

                        <!-- 予約者情報 (読み取り専用) -->
                        <v-col cols="12">
                            <v-card variant="text">
                                <v-card-title class="px-0">
                                    予約者情報
                                    <span class="text-caption text-grey ml-2">※変更不可</span>
                                </v-card-title>
                                <v-card-text class="px-0">
                                    <v-alert type="info" density="compact" variant="tonal" class="mb-4">
                                        ご登録情報は<a :href="`/shops/${props.shop.slug}/booker/profile/edit`"
                                           style="color: inherit; text-decoration: underline;">
                                            プロフィール編集
                                        </a>から変更可能です。
                                    </v-alert>

                                    <v-text-field v-model="form.booker_name" name="booker_name" label="お名前 *" readonly
                                                  required variant="filled"
                                                  density="compact"></v-text-field>

                                    <v-text-field v-model="form.contact_email" name="contact_email" label="メールアドレス *"
                                                  type="email" readonly
                                                  required variant="filled" density="compact"></v-text-field>

                                    <v-text-field v-model="form.contact_phone" name="contact_phone" label="電話番号 *"
                                                  type="tel" readonly required
                                                  variant="filled" density="compact"></v-text-field>

                                    <v-textarea v-if="form.shop_memo" v-model="form.shop_memo" label="登録済みの備考" readonly
                                                variant="filled"
                                                density="compact" rows="2" auto-grow hide-details></v-textarea>
                                </v-card-text>
                            </v-card>
                        </v-col>

                    </v-row>

                    <!-- 確認ダイアログ -->
                    <v-dialog v-model="confirmDialog" max-width="500px">
                        <v-card>
                            <v-card-title class="bg-primary text-white py-4">
                                予約内容の確認
                            </v-card-title>

                            <v-card-text>
                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-text class="pa-0 text-subtitle-2 text-grey-darken-1">
                                        予約日時
                                    </v-card-text>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ displayDateTime }} ({{ totalDuration }}分)
                                    </v-card-text>
                                </v-card>

                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">
                                        メニュー
                                    </v-card-title>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ selectedMenu?.name }}
                                    </v-card-text>
                                </v-card>

                                <v-card v-if="form.option_ids.length > 0" variant="text" class="px-0 mb-4">
                                    <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">
                                        オプション
                                    </v-card-title>
                                    <v-card-text class="pa-0 text-body-1">
                                        <div v-for="optId in form.option_ids" :key="optId">
                                            {{availableOptions.find(o => o.id === optId)?.name}}
                                        </div>
                                    </v-card-text>
                                </v-card>

                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">
                                        担当スタッフ
                                    </v-card-title>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ selectedStaffName }}
                                    </v-card-text>
                                </v-card>

                                <v-card v-if="form.note_from_booker" variant="text" class="px-0 mb-4">
                                    <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">
                                        予約メモ
                                    </v-card-title>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ form.note_from_booker }}
                                    </v-card-text>
                                </v-card>

                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">
                                        合計金額（税込）
                                    </v-card-title>
                                    <v-card-text class="pa-0 text-body-1 font-weight-bold">
                                        {{ totalPrice.toLocaleString() }}円
                                    </v-card-text>
                                </v-card>

                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">
                                        キャンセル期限
                                    </v-card-title>
                                    <v-card-text class="pa-0 text-body-1">
                                        {{ cancellationDeadline ?? '-' }} まで
                                    </v-card-text>
                                </v-card>

                                <v-card variant="text" class="px-0 mb-4">
                                    <v-card-title class="pa-0 text-subtitle-2 text-grey-darken-1">
                                        ※キャンセル規定について
                                    </v-card-title>
                                    <v-card-text class="pa-0 text-body-2">
                                        上記期限を過ぎてのキャンセルにはキャンセル料が発生する場合がございます。<br>
                                        詳細は店舗までお問い合わせください。
                                    </v-card-text>
                                </v-card>
                            </v-card-text>

                            <v-card-actions class="pa-4">
                                <v-btn variant="text" @click="confirmDialog = false">
                                    戻って修正する
                                </v-btn>
                                <v-spacer></v-spacer>
                                <v-btn color="primary" variant="elevated" @click="submitBookingConfirmed">
                                    確定する
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
                </form>
            </v-container>
        </v-main>

        <BookingStickyFooter :menu-name="selectedMenu?.name" :staff-name="selectedStaffName"
                             :date-time="displayDateTime"
                             :total-price="totalPrice" submit-label="確認画面へ進む" :disabled="!isFormValid" max-width="600px"
                             mobile-layout
                             @submit="openConfirmDialog" />
    </v-app>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import ShopHeader from "@/components/common/ShopHeader.vue";
import BookingStickyFooter from "@/components/common/BookingStickyFooter.vue";

// --- 型定義 ---
interface BusinessHour {
    weekday: number;
    start_time: string;
    end_time: string;
    is_closed: boolean;
}
interface SpecialDay {
    date: string;
    start_time: string;
    end_time: string;
}
interface Shop {
    id: number;
    name: string;
    slug: string;
    businessHoursRegular: BusinessHour[];
    specialOpenDays: SpecialDay[];
    specialClosedDays: SpecialDay[];
    timezone?: string;
}
interface Menu {
    id: number;
    name: string;
    price: number;
    duration: number;
    options: Option[];
    requires_staff_assignment: boolean;
    staffs: Staff[];
}
interface Option {
    id: number;
    name: string;
    price: number;
    additional_duration: number;
}
interface StaffSchedule {
    weekday: number;
    start_time: string;
    end_time: string;
    is_closed?: boolean;
}
interface Staff {
    id: number;
    profile: {
        nickname: string;
        small_image_url: string | null;
    };
    schedules: StaffSchedule[];
}
interface ShopBooker {
    id: number;
    name: string;
    contact_email: string;
    contact_phone: string;
    crm?: {
        shop_memo: string | null;
    };
}
interface Booking {
    id: number;
    start_at: string;
    end_at: string;
    assigned_staff_id: number;
}
interface Props {
    shop: Shop;
    menus: Menu[];
    staffs: Staff[];
    booker: ShopBooker; // Logged-in booker
    bookings: Booking[];
    errors: string[];
    oldInput: { [key: string]: any } | null;
    csrfToken: string;
}

// --- Props定義 ---
const props = defineProps<Props>();

// --- フォームの状態 ---
const form = ref({
    start_at: "",
    menu_id: null as number | null,
    option_ids: [] as number[],
    assigned_staff_id: null as number | null,
    booker_name: props.booker?.name ?? "",
    contact_email: props.booker?.contact_email ?? "",
    contact_phone: props.booker?.contact_phone ?? "",
    shop_memo: props.booker?.crm?.shop_memo ?? "",
    note_from_booker: "",
});
const selectedTime = ref<string | null>(null);
const assignedStaffs = ref<Staff[]>([]);
const staffWarning = ref<string | null>(null);
const shiftWarning = ref<string | null>(null);
const conflictWarning = ref<string | null>(null);
const cancellationDeadline = ref<string | null>(null);
const confirmDialog = ref(false);

// --- カレンダーの状態 ---
const workingDays = ref<string[]>([]); // YYYY-MM-DD strings
const pickerYear = ref(new Date().getFullYear());
const pickerMonth = ref(new Date().getMonth() + 1);

// --- 時間枠の状態 ---
const timeSlots = ref<string[]>([]);
const groupedTimeSlots = computed(() => {
    const groups: { [key: string]: string[] } = {};
    timeSlots.value.forEach((time) => {
        const hour = time.split(":")[0];
        if (!groups[hour]) {
            groups[hour] = [];
        }
        groups[hour].push(time);
    });

    return Object.keys(groups).sort((a, b) => Number(a) - Number(b)).map(hour => ({
        hour,
        slots: groups[hour]
    }));
});

const selectedDateValue = ref<Date | null>(null);
const setDate = (date: Date) => {
    selectedDateValue.value = date;
};

const formattedSelectedDate = computed({
    get() {
        if (!selectedDateValue.value) return "";
        const d = new Date(selectedDateValue.value);
        const year = d.getFullYear();
        const month = (`0` + (d.getMonth() + 1)).slice(-2);
        const day = (`0` + d.getDate()).slice(-2);
        return `${year}-${month}-${day}`;
    },
    set(value) {
        if (value && /^\d{4}-\d{2}-\d{2}$/.test(value)) {
            setDate(new Date(value));
        }
    },
});

// --- API呼び出し ---
const fetchTimeSlots = async () => {
    if (!form.value.menu_id || !form.value.assigned_staff_id || !formattedSelectedDate.value) {
        timeSlots.value = [];
        return;
    }

    try {
        const response = await axios.get(
            `/shops/${props.shop.slug}/booker/api/available-slots`,
            {
                params: {
                    date: formattedSelectedDate.value,
                    menu_id: form.value.menu_id,
                    option_ids: form.value.option_ids,
                    staff_id: form.value.assigned_staff_id,
                },
            }
        );
        timeSlots.value = response.data;
    } catch (error) {
        console.error("予約枠の取得に失敗しました:", error);
        timeSlots.value = [];
    }
};

const fetchAssignedStaffs = async () => {
    if (!form.value.menu_id) {
        assignedStaffs.value = [];
        return;
    }
    try {
        const url = `/shops/${props.shop.slug}/booker/api/menus/${form.value.menu_id}/staffs`;
        const response = await axios.get(url);
        assignedStaffs.value = response.data.staffs;
    } catch (error) {
        console.error("割り当てスタッフの取得に失敗しました:", error);
        assignedStaffs.value = [];
    }
};

const fetchWorkingDays = async (year: number, month: number) => {
    if (!form.value.assigned_staff_id) {
        workingDays.value = [];
        return;
    }

    const yearMonth = `${year}-${String(month).padStart(2, '0')}`;

    try {
        const response = await axios.get(
            `/shops/${props.shop.slug}/booker/api/staffs/${form.value.assigned_staff_id}/working-days`,
            {
                params: { year_month: yearMonth, menu_id: form.value.menu_id }
            }
        );
        workingDays.value = response.data;
    } catch (error) {
        console.error("シフトデータの取得に失敗しました:", error);
    }
};

// --- バリデーション ---
const checkShiftAndConflict = async () => {
    shiftWarning.value = null;
    conflictWarning.value = null;
    if (!form.value.start_at || !form.value.assigned_staff_id || !form.value.menu_id) return;

    try {
        const params = {
            menu_id: form.value.menu_id,
            assigned_staff_id: form.value.assigned_staff_id,
            start_at: form.value.start_at,
            option_ids: form.value.option_ids,
        };
        const baseUrl = `/shops/${props.shop.slug}/booker/api/bookings`;

        const [shiftRes, conflictRes, deadlineRes] = await Promise.all([
            axios.get(`${baseUrl}/validate-shift`, { params }),
            axios.get(`${baseUrl}/validate-conflict`, { params }),
            axios.get(`${baseUrl}/cancellation-deadline`, { params: { menu_id: form.value.menu_id, start_at: form.value.start_at } }),
        ]);

        if (!shiftRes.data.valid) {
            shiftWarning.value = "※この時間はスタッフのシフト外です";
        }
        if (!conflictRes.data.valid) {
            conflictWarning.value = "※この時間は既に予約が入っています";
        }
        // キャンセル期限を取得
        if (deadlineRes.data.cancellation_deadline) {
            cancellationDeadline.value = deadlineRes.data.cancellation_deadline;
        }
    } catch (error) {
        console.error("バリデーションに失敗しました:", error);
    }
};

const checkStaffAssignment = async () => {
    staffWarning.value = null;
    if (!form.value.menu_id || !form.value.assigned_staff_id) return;
    if (selectedMenu.value && !selectedMenu.value.requires_staff_assignment) return;

    try {
        const response = await axios.get(
            `/shops/${props.shop.slug}/booker/api/bookings/validate-staff`,
            {
                params: {
                    menu_id: form.value.menu_id,
                    assigned_staff_id: form.value.assigned_staff_id,
                },
            }
        );
        if (!response.data.valid) {
            staffWarning.value = "※このスタッフはメニューに割り当たっていません";
        }
    } catch (error) {
        console.error("スタッフのバリデーションに失敗しました:", error);
    }
};

// --- ヘルパー関数 ---
const allowedDates = (date: unknown): boolean => {
    const dateString = getDateString(date);
    if (!dateString) return false;
    return workingDays.value.includes(dateString);
};

const getDateString = (dateInput: unknown): string | null => {
    let d: Date | null = null;
    if (dateInput instanceof Date) d = dateInput;
    else if (typeof dateInput === 'string' || typeof dateInput === 'number') d = new Date(dateInput);
    if (!d || isNaN(d.getTime())) return null;
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const onPickerYearChange = (year: number) => {
    pickerYear.value = year;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};
const onPickerMonthChange = (month: number) => {
    pickerMonth.value = month + 1;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};

// --- Computedプロパティ ---
const selectedMenu = computed((): Menu | undefined =>
    props.menus.find((m) => m.id === form.value.menu_id)
);
const availableOptions = computed(
    (): Option[] => selectedMenu.value?.options ?? []
);
const availableStaffs = computed((): Staff[] => {
    if (!form.value.menu_id) return [];
    if (selectedMenu.value && !selectedMenu.value.requires_staff_assignment) return props.staffs;
    return assignedStaffs.value;
});
const selectedStaffName = computed(() => {
    if (!form.value.assigned_staff_id) return undefined;
    const staff = availableStaffs.value.find(s => s.id === form.value.assigned_staff_id);
    return staff?.profile.nickname;
});
const totalDuration = computed(() => {
    let total = selectedMenu.value?.duration ?? 0;
    const selectedOptions = availableOptions.value.filter(opt => form.value.option_ids.includes(opt.id));
    selectedOptions.forEach(opt => total += opt.additional_duration);
    return total;
});
const totalPrice = computed(() => {
    let total = selectedMenu.value?.price ?? 0;
    const selectedOptions = availableOptions.value.filter(opt => form.value.option_ids.includes(opt.id));
    selectedOptions.forEach(opt => total += opt.price);
    return total;
});

const displayDateTime = computed(() => {
    if (!formattedSelectedDate.value || !selectedTime.value) return undefined;
    const [hours, minutes] = selectedTime.value.split(":").map(Number);
    const date = new Date(selectedDateValue.value!);
    date.setHours(hours, minutes + totalDuration.value, 0);
    const endStr = `${String(date.getHours()).padStart(2, "0")}:${String(date.getMinutes()).padStart(2, "0")}`;
    return `${formattedSelectedDate.value} ${selectedTime.value}~${endStr}`;
});

const isFormValid = computed(() => {
    return (
        !!form.value.menu_id &&
        !!form.value.assigned_staff_id &&
        !!form.value.start_at &&
        !shiftWarning.value &&
        !conflictWarning.value &&
        !staffWarning.value
    );
});

const openConfirmDialog = () => {
    confirmDialog.value = true;
};

const submitBookingConfirmed = () => {
    const formElement = document.getElementById("booking-create-form") as HTMLFormElement;
    if (formElement) formElement.submit();
};

// --- ウォッチャー ---
watch([() => form.value.menu_id, () => form.value.option_ids, () => form.value.assigned_staff_id, () => formattedSelectedDate.value],
    fetchTimeSlots
);

watch(() => form.value.menu_id, () => {
    form.value.option_ids = [];
    selectedTime.value = null;
    form.value.assigned_staff_id = null;
    if (selectedMenu.value?.requires_staff_assignment) fetchAssignedStaffs();
    else assignedStaffs.value = [];
});

watch(() => form.value.assigned_staff_id, (newStaffId) => {
    if (newStaffId) fetchWorkingDays(pickerYear.value, pickerMonth.value);
    else workingDays.value = [];
    checkStaffAssignment();
});

watch([() => formattedSelectedDate.value, selectedTime], async ([newDate, chipTime]) => {
    if (newDate && chipTime) {
        form.value.start_at = `${newDate} ${chipTime}:00`;
        await checkShiftAndConflict();
    } else {
        form.value.start_at = "";
    }
});

// --- 初期化 ---
onMounted(async () => {
    // 古い入力値の復元
    const hasOldInput = props.oldInput && Object.keys(props.oldInput).length > 0;
    if (hasOldInput) {
        const old = props.oldInput as { [key: string]: any };
        form.value.menu_id = old.menu_id ? Number(old.menu_id) : null;
        form.value.option_ids = (old.option_ids ?? []).map(Number);
        form.value.assigned_staff_id = old.assigned_staff_id ? Number(old.assigned_staff_id) : null;
        form.value.note_from_booker = old.note_from_booker ?? "";

        if (old.start_at) {
            const d = new Date(old.start_at);
            setDate(d);
            const time = (`0` + d.getHours()).slice(-2) + ":" + (`0` + d.getMinutes()).slice(-2);
            selectedTime.value = time;
        }
    }

    if (form.value.menu_id) await fetchAssignedStaffs();
    if (form.value.assigned_staff_id) fetchWorkingDays(pickerYear.value, pickerMonth.value);
    await fetchTimeSlots();
});
</script>

<style scoped>
.container-width-600 {
    max-width: 600px;
}
</style>
