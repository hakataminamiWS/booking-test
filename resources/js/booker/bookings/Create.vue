<template>
    <v-container>
        <!-- Navigation -->
        <v-row>
            <v-col cols="12">
                <v-btn
                       :href="`/shops/${props.shop.slug}/booker/bookings`"
                       prepend-icon="mdi-arrow-left"
                       variant="text">
                    予約履歴に戻る
                </v-btn>
            </v-col>
        </v-row>

        <!-- Shop Header -->
        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="shop" />
            </v-col>
        </v-row>

        <!-- Main Form Card -->
        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title>新規予約</v-card-title>
                    <v-card-text>
                        <form
                              :action="`/shops/${props.shop.slug}/booker/bookings`"
                              method="POST">
                            <input
                                   type="hidden"
                                   name="_token"
                                   :value="props.csrfToken" />
                            <input
                                   type="hidden"
                                   name="start_at"
                                   :value="form.start_at" />

                            <!-- Validation Errors -->
                            <v-alert
                                     v-if="props.errors.length > 0"
                                     type="error"
                                     class="mb-4">
                                <ul>
                                    <li
                                        v-for="(error, i) in props.errors"
                                        :key="i">
                                        {{ error }}
                                    </li>
                                </ul>
                            </v-alert>

                            <!-- Menu Selection -->
                            <p class="text-subtitle-1 font-weight-bold mt-4">
                                メニュー
                            </p>
                            <v-select v-model="form.menu_id" name="menu_id" :items="props.menus" item-title="name"
                                      item-value="id" label="メニュー *"
                                      required class="mb-2"></v-select>

                            <!-- Options -->
                            <v-select v-model="form.option_ids" :items="availableOptions" item-title="name"
                                      item-value="id" label="オプション" multiple
                                      chips closable-chips :disabled="!form.menu_id" class="mb-2"></v-select>
                            <input v-for="optId in form.option_ids" :key="optId" type="hidden" name="option_ids[]"
                                   :value="optId" />

                            <p class="text-subtitle-1">
                                合計: {{ totalDuration }}分 /
                                {{ totalPrice.toLocaleString() }}円
                            </p>
                            <v-divider class="my-6"></v-divider>

                            <!-- Staff Selection -->
                            <p class="text-subtitle-1 font-weight-bold">
                                担当スタッフ *
                            </p>
                            <v-select v-model="form.assigned_staff_id" name="assigned_staff_id" :items="availableStaffs"
                                      item-title="profile.nickname" item-value="id" label="担当スタッフ *"
                                      :disabled="!form.menu_id"></v-select>
                            <v-divider class="my-6"></v-divider>

                            <!-- Date Selection -->
                            <p class="text-subtitle-1 font-weight-bold">
                                予約日時
                            </p>
                            <v-text-field v-model="formattedSelectedDate" label="予約日付 *"
                                          @click="dateDialog = true"
                                          append-inner-icon="mdi-calendar" readonly></v-text-field>

                            <p class="text-caption mt-4">予約時間 *</p>
                            <v-sheet class="pa-2" border rounded min-height="68">
                                <div v-if="isLoading" class="d-flex justify-center align-center fill-height">
                                    <v-progress-circular indeterminate color="primary"></v-progress-circular>
                                </div>
                                <div v-else-if="groupedTimeSlots.length > 0">
                                    <div v-for="group in groupedTimeSlots" :key="group.hour"
                                         class="d-flex align-center py-1"
                                         style="border-bottom: 1px solid #eee">
                                        <div class="text-body-2 font-weight-bold mr-4" style="width: 40px">
                                            {{ group.hour }}時
                                        </div>
                                        <v-chip-group v-model="selectedTime" column mandatory active-class="primary">
                                            <v-chip v-for="time in group.slots" :key="time" :value="time"
                                                    variant="outlined" size="default" class="px-3">
                                                <v-icon v-if="selectedTime === time" start size="small">mdi-check</v-icon>
                                                {{ time }}
                                            </v-chip>
                                        </v-chip-group>
                                    </div>
                                </div>
                                <div v-else class="d-flex justify-center align-center fill-height text-grey-darken-1">
                                    <p v-if="!form.menu_id">先にメニューを選択してください</p>
                                    <p v-else-if="!form.assigned_staff_id">担当スタッフを選択してください</p>
                                    <p v-else-if="!formattedSelectedDate">予約日を選択してください</p>
                                    <p v-else>予約可能な時間帯がありません。</p>
                                </div>
                            </v-sheet>

                            <!-- Selection Summary Banner -->
                            <v-fade-transition>
                                <v-alert v-if="selectedTime" color="primary" variant="tonal" class="mt-4 mb-2" border="start">
                                    <div class="d-flex align-center">
                                        <v-icon color="primary" class="mr-3" size="large">mdi-clock-check-outline</v-icon>
                                        <div>
                                            <div class="text-caption">選択した予約時間</div>
                                            <div class="text-h6 font-weight-bold">
                                                {{ selectedTime }} 〜 {{ calculatedEndTime }}
                                                <span class="text-body-2 font-weight-medium ml-2">({{ totalDuration }}分)</span>
                                            </div>
                                        </div>
                                    </div>
                                </v-alert>
                            </v-fade-transition>
                            <v-divider class="my-6"></v-divider>

                            <!-- Note -->
                            <p class="text-subtitle-1 font-weight-bold">
                                備考
                            </p>
                            <v-textarea v-model="form.note_from_booker" name="note_from_booker" label="予約に関するご要望"
                                        rows="3"></v-textarea>

                            <!-- Error Message -->
                            <v-alert v-if="bookingError" type="error" density="compact" variant="tonal" class="mb-4">
                                {{ bookingError }}
                            </v-alert>

                            <!-- Actions -->
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="primary" variant="elevated" size="large"
                                       :disabled="!form.start_at || !form.menu_id || !form.assigned_staff_id || !!bookingError"
                                       @click="confirmDialog = true">
                                    予約内容を確認する
                                </v-btn>
                            </v-card-actions>

                            <!-- Confirmation Dialog -->
                            <v-dialog v-model="confirmDialog" max-width="500px">
                                <v-card>
                                    <v-card-title class="bg-primary text-white py-4">予約内容の最終確認</v-card-title>
                                    <v-card-text class="pa-6">
                                        <div class="text-subtitle-2 text-grey-darken-1 mb-1">予約日時</div>
                                        <div class="text-h6 font-weight-bold mb-4">
                                            {{ formattedSelectedDate }}<br>
                                            {{ selectedTime }} 〜 {{ calculatedEndTime }} ({{ totalDuration }}分)
                                        </div>

                                        <v-divider class="mb-4"></v-divider>

                                        <div class="text-subtitle-2 text-grey-darken-1 mb-1">メニュー</div>
                                        <div class="text-body-1 font-weight-bold mb-4">
                                            {{ selectedMenu?.name }}
                                        </div>

                                        <div v-if="form.option_ids.length > 0">
                                            <div class="text-subtitle-2 text-grey-darken-1 mb-1">オプション</div>
                                            <div class="text-body-2 mb-4">
                                                <div v-for="optId in form.option_ids" :key="optId">
                                                    ・{{ availableOptions.find(o => o.id === optId)?.name }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-subtitle-2 text-grey-darken-1 mb-1">担当スタッフ</div>
                                        <div class="text-body-1 mb-4">
                                            {{ selectedStaff?.profile?.nickname }}
                                        </div>

                                        <v-divider class="mb-4"></v-divider>

                                        <div class="d-flex justify-space-between align-center py-2">
                                            <span class="text-subtitle-1 font-weight-bold">合計金額（税込）</span>
                                            <span class="text-h5 text-primary font-weight-bold">{{ totalPrice.toLocaleString() }}円</span>
                                        </div>

                                        <p class="text-caption text-error mt-4">
                                            ※予約を確定するとキャンセルには期限があります。内容を今一度ご確認ください。
                                        </p>
                                    </v-card-text>
                                    <v-divider></v-divider>
                                    <v-card-actions class="pa-4">
                                        <v-btn variant="text" @click="confirmDialog = false">戻って修正する</v-btn>
                                        <v-spacer></v-spacer>
                                        <v-btn color="primary" variant="elevated" @click="submitBooking">
                                            この内容で予約を確定する
                                        </v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-dialog>
                        </form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Date Selection Dialog -->
        <v-dialog v-model="dateDialog" max-width="320px">
            <v-date-picker v-model="selectedDateValue" @update:model-value="updateDateFromPicker"
                           @update:year="onPickerYearChange" @update:month="onPickerMonthChange"
                           :allowed-dates="allowedDates"
                           show-adjacent-months></v-date-picker>
        </v-dialog>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import axios from "axios";
import ShopHeader from "@/components/common/ShopHeader.vue";

// --- Type Definitions ---
interface Shop {
    id: number;
    name: string;
    slug: string;
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
interface Staff {
    id: number;
    profile: { nickname: string };
}
interface ShopBooker {
    id: number;
    name: string;
}
interface Booking {
    id: number;
    start_at: string;
    end_at: string;
    assigned_staff_id: number;
}
interface Props {
    shop: Shop;
    booker: ShopBooker;
    menus: Menu[];
    staffs: Staff[];
    bookings: Booking[];
    errors: string[];
    oldInput: { [key: string]: any } | null;
    csrfToken: string;
}

// --- Props ---
const props = defineProps<Props>();

// --- Form State ---
const form = ref({
    start_at: "",
    menu_id: null as number | null,
    option_ids: [] as number[],
    assigned_staff_id: null as number | null,
    note_from_booker: "",
});
const selectedTime = ref<string | null>(null);

// --- State ---
const assignedStaffs = ref<Staff[]>([]);
const timeSlots = ref<string[]>([]);
const bookingError = ref<string | null>(null);
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
const isLoading = ref(false);
const selectedDateValue = ref<Date | null>(new Date());
const workingDays = ref<string[]>([]); // YYYY-MM-DD strings
const pickerYear = ref(new Date().getFullYear());
const pickerMonth = ref(new Date().getMonth() + 1);
const dateDialog = ref(false);
const confirmDialog = ref(false);

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
            selectedDateValue.value = new Date(value);
        }
    },
});

const fetchTimeSlots = async () => {
    if (!form.value.menu_id || !form.value.assigned_staff_id || !formattedSelectedDate.value) {
        timeSlots.value = [];
        return;
    }

    isLoading.value = true;
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
    } finally {
        isLoading.value = false;
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
                params: {
                    year_month: yearMonth,
                    menu_id: form.value.menu_id,
                }
            }
        );
        workingDays.value = response.data;
    } catch (error) {
        console.error("Shift data fetch failed:", error);
    }
};

const allowedDates = (date: unknown): boolean => {
    const dateString = getDateString(date);
    if (!dateString) return false;

    // workingDays に含まれているかどうか
    return workingDays.value.includes(dateString);
};

// Helper: 統一的な日付文字列取得 (YYYY-MM-DD)
const getDateString = (dateInput: unknown): string | null => {
    let d: Date | null = null;

    if (dateInput instanceof Date) {
        d = dateInput;
    } else if (typeof dateInput === 'string' || typeof dateInput === 'number') {
        d = new Date(dateInput);
    } else if (dateInput && typeof dateInput === 'object') {
        const val = (dateInput as any).value || (dateInput as any).date;
        if (val) {
            if (val instanceof Date) d = val;
            else d = new Date(val);
        } else {
            return null;
        }
    }

    if (!d || isNaN(d.getTime())) return null;

    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Picker navigation handlers
const onPickerYearChange = (year: number) => {
    pickerYear.value = year;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};
const onPickerMonthChange = (month: number) => {
    pickerMonth.value = month + 1;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};

const validateBooking = async () => {
    bookingError.value = null;
    if (!form.value.menu_id || !form.value.assigned_staff_id || !form.value.start_at) return;

    try {
        const params = {
            menu_id: form.value.menu_id,
            assigned_staff_id: form.value.assigned_staff_id,
            start_at: form.value.start_at,
            option_ids: form.value.option_ids,
        };

        const baseUrl = `/shops/${props.shop.slug}/booker/api/bookings`;

        // 1. スタッフのメニュー対応チェック
        const staffRes = await axios.get(`${baseUrl}/validate-staff`, { params });
        if (!staffRes.data.valid) {
            bookingError.value = "選択されたスタッフはこのメニューを担当できません。";
            return;
        }

        // 2. シフト内チェック
        const shiftRes = await axios.get(`${baseUrl}/validate-shift`, { params });
        if (!shiftRes.data.valid) {
            bookingError.value = "選択された時間はスタッフの勤務時間外です。";
            return;
        }

        // 3. 重複チェック
        const conflictRes = await axios.get(`${baseUrl}/validate-conflict`, { params });
        if (!conflictRes.data.valid) {
            bookingError.value = "選択された時間は既に他の予約が入っています。";
            return;
        }
    } catch (error) {
        console.error("バリデーション中にエラーが発生しました:", error);
    }
};

watch(
    () => form.value.menu_id,
    (newVal) => {
        form.value.option_ids = [];
        form.value.assigned_staff_id = null;
        selectedTime.value = null;
        if (newVal) {
            if (selectedMenu.value?.requires_staff_assignment) {
                fetchAssignedStaffs();
            } else {
                assignedStaffs.value = [];
            }
        } else {
            assignedStaffs.value = [];
        }
    }
);

watch(
    [
        () => form.value.menu_id,
        () => form.value.option_ids,
        () => form.value.assigned_staff_id,
        () => formattedSelectedDate.value,
    ],
    () => {
        selectedTime.value = null;
        if (form.value.menu_id && form.value.assigned_staff_id && formattedSelectedDate.value) {
            fetchTimeSlots();
        }
    }
);

watch(() => form.value.assigned_staff_id, () => {
    if (form.value.assigned_staff_id) {
        fetchWorkingDays(pickerYear.value, pickerMonth.value);
    } else {
        workingDays.value = [];
    }
});

watch(dateDialog, (isOpen) => {
    if (isOpen && form.value.assigned_staff_id) {
        if (selectedDateValue.value) {
            const d = new Date(selectedDateValue.value);
            pickerYear.value = d.getFullYear();
            pickerMonth.value = d.getMonth() + 1;
        }
        fetchWorkingDays(pickerYear.value, pickerMonth.value);
    }
});

watch(selectedTime, (newVal) => {
    if (newVal && formattedSelectedDate.value) {
        form.value.start_at = `${formattedSelectedDate.value} ${newVal}`;
        validateBooking();
    } else {
        form.value.start_at = "";
        bookingError.value = null;
    }
});

// --- Computed Properties ---
const selectedMenu = computed((): Menu | undefined =>
    props.menus.find((m) => m.id === form.value.menu_id)
);
const availableOptions = computed(
    (): Option[] => selectedMenu.value?.options ?? []
);
const availableStaffs = computed((): Staff[] => {
    if (!form.value.menu_id) return [];
    if (selectedMenu.value && !selectedMenu.value.requires_staff_assignment) {
        return props.staffs;
    }
    return assignedStaffs.value;
});
const selectedStaff = computed((): Staff | undefined =>
    availableStaffs.value.find((s) => s.id === form.value.assigned_staff_id)
);

const totalPrice = computed(() => {
    let total = selectedMenu.value?.price ?? 0;
    const selectedOptions = availableOptions.value.filter((opt) =>
        form.value.option_ids.includes(opt.id)
    );
    selectedOptions.forEach((opt) => {
        total += opt.price;
    });
    return total;
});

const totalDuration = computed(() => {
    let total = selectedMenu.value?.duration ?? 0;
    const selectedOptions = availableOptions.value.filter((opt) =>
        form.value.option_ids.includes(opt.id)
    );
    selectedOptions.forEach((opt) => {
        total += opt.additional_duration;
    });
    return total;
});

const calculatedEndTime = computed(() => {
    if (!selectedTime.value) return "";
    const [hours, minutes] = selectedTime.value.split(":").map(Number);
    const date = new Date();
    date.setHours(hours, minutes + totalDuration.value, 0);
    return `${String(date.getHours()).padStart(2, "0")}:${String(date.getMinutes()).padStart(2, "0")}`;
});

const submitBooking = () => {
    // フォームを送信
    const formEl = document.querySelector('form');
    if (formEl) {
        formEl.submit();
    }
};

// --- Dialog State ---

const updateDateFromPicker = (date: Date | null) => {
    if (date) {
        selectedDateValue.value = date;
    }
    dateDialog.value = false;
};
</script>
