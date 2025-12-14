<template>
    <v-container>
        <!-- Navigation -->
        <v-row>
            <v-col cols="12">
                <v-btn
                    :href="`/owner/shops/${props.shop.slug}/bookings`"
                    prepend-icon="mdi-arrow-left"
                >
                    予約一覧に戻る
                </v-btn>
            </v-col>
        </v-row>

        <!-- Shop Header -->
        <ShopHeader :shop="props.shop" />

        <!-- Main Form Card -->
        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title>予約編集</v-card-title>
                    <v-card-text>
                        <form
                            :action="`/owner/shops/${props.shop.slug}/bookings/${props.booking.id}`"
                            method="POST"
                        >
                            <input
                                type="hidden"
                                name="_token"
                                :value="props.csrfToken"
                            />
                            <input type="hidden" name="_method" value="PUT" />
                            <input
                                type="hidden"
                                name="start_at"
                                :value="form.start_at"
                            />
                            <!-- shop_booker_id は編集不可なので hidden で送る必要はないが、もし必要なら含める -->
                            <input
                                type="hidden"
                                name="shop_booker_id"
                                :value="form.shop_booker_id ?? ''"
                            />

                            <!-- Validation Errors -->
                            <v-alert
                                v-if="props.errors.length > 0"
                                type="error"
                                class="mb-4"
                            >
                                <ul>
                                    <li
                                        v-for="(error, i) in props.errors"
                                        :key="i"
                                    >
                                        {{ error }}
                                    </li>
                                </ul>
                            </v-alert>

                            <!-- Booker Info (Readonly) -->
                            <p class="text-subtitle-1 font-weight-bold mt-4">
                                予約者 (編集不可)
                            </p>
                            <v-text-field
                                v-if="!!form.booker_number"
                                v-model="form.booker_number"
                                label="会員番号"
                                readonly
                                variant="filled"
                            ></v-text-field>
                            <v-text-field
                                v-model="form.booker_name"
                                name="booker_name"
                                label="予約者名"
                                readonly
                                variant="filled"
                            ></v-text-field>
                            <v-text-field
                                v-model="form.booker_name_kana"
                                name="booker_name_kana"
                                label="予約者のよみがな"
                                readonly
                                variant="filled"
                            ></v-text-field>

                            <!-- Contact Info (Editable) -->
                            <p class="text-subtitle-1 font-weight-bold mt-4">
                                連絡先 (編集可能)
                            </p>
                            <v-text-field
                                v-model="form.contact_email"
                                name="contact_email"
                                label="連絡先メールアドレス *"
                                type="email"
                                required
                            ></v-text-field>
                            <v-text-field
                                v-model="form.contact_phone"
                                name="contact_phone"
                                label="連絡先電話番号 *"
                                type="tel"
                                required
                            ></v-text-field>
                            <v-textarea
                                v-model="form.shop_memo"
                                name="shop_memo"
                                label="店舗側のメモ（予約者には表示されません）"
                                rows="3"
                            ></v-textarea>
                            <v-divider class="my-6"></v-divider>

                            <!-- Menu & Options -->
                            <p class="text-subtitle-1 font-weight-bold">
                                メニュー・オプション
                            </p>
                            <v-select
                                v-model="form.menu_id"
                                name="menu_id"
                                :items="props.menus"
                                item-title="name"
                                item-value="id"
                                label="メニュー *"
                                required
                                class="mb-2"
                            ></v-select>
                            <v-select
                                v-model="form.option_ids"
                                :items="availableOptions"
                                item-title="name"
                                item-value="id"
                                label="オプション"
                                multiple
                                chips
                                closable-chips
                                :disabled="!form.menu_id"
                                class="mb-2"
                            ></v-select>
                            <!-- 配列送信用の隠しフィールド -->
                            <input
                                v-for="optId in form.option_ids"
                                :key="optId"
                                type="hidden"
                                name="option_ids[]"
                                :value="optId"
                            />
                            <p class="text-subtitle-1">
                                合計: {{ totalDuration }}分 /
                                {{ totalPrice.toLocaleString() }}円
                            </p>
                            <v-divider class="my-6"></v-divider>

                            <!-- Staff Selection -->
                            <p class="text-subtitle-1 font-weight-bold">
                                担当スタッフ
                            </p>
                            <v-select
                                v-model="form.assigned_staff_id"
                                name="assigned_staff_id"
                                :items="availableStaffs"
                                item-title="profile.nickname"
                                item-value="id"
                                label="担当スタッフ *"
                                :disabled="!form.menu_id"
                                @update:focused="!$event && checkStaffAssignment()"
                            ></v-select>
                            <v-alert
                                v-if="staffWarning"
                                type="warning"
                                density="compact"
                                variant="tonal"
                                class="mb-2 mt-2"
                            >
                                {{ staffWarning }}
                            </v-alert>
                            <v-checkbox
                                v-model="showAllStaffs"
                                label="メニューに割り当たっていない担当スタッフも表示する"
                                :disabled="!form.menu_id"
                                density="compact"
                                class="mt-n4"
                            ></v-checkbox>
                            <v-divider class="my-6"></v-divider>

                            <!-- Date & Time Selection -->
                            <p class="text-subtitle-1 font-weight-bold">
                                予約日時
                            </p>
                            <v-text-field
                                v-model="formattedSelectedDate"
                                label="予約日付 *"
                                @click:append-inner="dateDialog = true"
                                append-inner-icon="mdi-calendar"
                                readonly
                            ></v-text-field>
                            <v-checkbox
                                v-model="allowOffShift"
                                label="担当スタッフのシフト外も選択可能にする"
                                density="compact"
                                class="mt-n4"
                            ></v-checkbox>

                            <p class="text-caption mt-4">予約時間 *</p>
                            <v-sheet
                                class="pa-2"
                                border
                                rounded
                                min-height="68"
                            >
                                <!-- ローディング表示 -->
                                <div
                                    v-if="isLoading"
                                    class="d-flex justify-center align-center fill-height"
                                >
                                    <v-progress-circular
                                        indeterminate
                                        color="primary"
                                    ></v-progress-circular>
                                </div>

                                <!-- タイムチップ表示 (時間ごとにグループ化) -->
                                <div
                                    v-else-if="
                                        groupedTimeSlots.length > 0
                                    "
                                >
                                    <div
                                        v-for="group in groupedTimeSlots"
                                        :key="group.hour"
                                        class="d-flex align-center py-1"
                                        style="border-bottom: 1px solid #eee"
                                    >
                                        <div
                                            class="text-body-2 font-weight-bold mr-4"
                                            style="width: 40px"
                                        >
                                            {{ group.hour }}時
                                        </div>
                                        <v-chip-group
                                            v-model="selectedTime"
                                            column
                                            mandatory
                                            active-class="primary"
                                        >
                                            <v-chip
                                                v-for="time in group.slots"
                                                :key="time"
                                                :value="time"
                                                variant="outlined"
                                                size="small"
                                            >
                                                {{ time }}
                                            </v-chip>
                                        </v-chip-group>
                                    </div>
                                </div>

                                <!-- 予約枠がない場合の表示 -->
                                <div
                                    v-else
                                    class="d-flex justify-center align-center fill-height text-grey-darken-1"
                                >
                                    <p v-if="!form.menu_id">
                                        先にメニューを選択してください
                                    </p>
                                    <p v-else-if="!form.assigned_staff_id">
                                        担当スタッフを選択してください
                                    </p>
                                    <p v-else-if="!selectedDateValue">
                                        予約日を選択してください
                                    </p>
                                    <p v-else>予約可能な時間帯がありません。</p>
                                </div>
                            </v-sheet>

                            <v-text-field
                                v-model="directTimeInput"
                                label="直接入力（シフト外の入力も可能です）"
                                placeholder="HH:MM"
                                append-inner-icon="mdi-clock-outline"
                                class="mt-4"
                                style="max-width: 200px"
                                @click:append-inner="timePickerDialog = true"
                                @blur="onDirectTimeBlur"
                            ></v-text-field>
                            <v-alert
                                v-if="shiftWarning"
                                type="warning"
                                density="compact"
                                variant="tonal"
                                class="mb-2"
                            >
                                {{ shiftWarning }}
                            </v-alert>
                            <v-alert
                                v-if="conflictWarning"
                                type="error"
                                density="compact"
                                variant="tonal"
                                class="mb-2"
                            >
                                {{ conflictWarning }}
                            </v-alert>
                            <v-divider class="my-6"></v-divider>

                            <!-- Memo -->
                            <p class="text-subtitle-1 font-weight-bold">
                                予約時メモ
                            </p>
                            <v-textarea
                                v-model="form.note_from_booker"
                                name="note_from_booker"
                                label="予約に関するメモ"
                                rows="3"
                            ></v-textarea>

                            <!-- Actions -->
                            <v-card-actions>
                                <v-btn
                                    color="error"
                                    @click="deleteDialog = true"
                                >
                                    予約を削除する
                                </v-btn>
                                <v-spacer></v-spacer>
                                <v-btn
                                    type="submit"
                                    color="primary"
                                    :disabled="!isFormValid"
                                    @click="validateAndSubmit"
                                >
                                    更新する
                                </v-btn>
                            </v-card-actions>
                        </form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Date Selection Dialog -->
        <v-dialog v-model="dateDialog" max-width="320px">
            <v-date-picker
                v-model="selectedDateValue"
                @update:model-value="updateDateFromPicker"
                @update:year="onPickerYearChange"
                @update:month="onPickerMonthChange"
                :allowed-dates="allowedDates"
                show-adjacent-months
            >
                <template v-slot:day="{ item, props: dayProps }">
                    <v-btn
                        v-bind="dayProps"
                        :style="getDayStyle(item)"
                        class="d-flex justify-center align-center"
                        style="position: relative;"
                        variant="text" 
                        size="small"
                        rounded="circle"
                    >
                         {{ getDayNumber(item) }}
                         <div
                            v-if="isWorkingDay(item) && allowOffShift"
                            style="
                                position: absolute;
                                bottom: 2px;
                                left: 50%;
                                transform: translateX(-50%);
                                width: 4px;
                                height: 4px;
                                border-radius: 50%;
                                background-color: #1976D2;
                            "
                         ></div>
                    </v-btn>
                </template>
            </v-date-picker>
        </v-dialog>

        <!-- Time Picker Dialog -->
        <v-dialog v-model="timePickerDialog" width="auto">
            <v-card>
                <v-time-picker
                    v-model="directTimeInput"
                    format="24hr"
                ></v-time-picker>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="primary"
                        variant="text"
                        @click="timePickerDialog = false"
                    >
                        完了
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="500px">
            <v-card>
                <v-card-title class="text-h5">
                    本当に削除しますか？
                </v-card-title>
                <v-card-text>
                    この操作は元に戻せません。この予約は完全に削除されます。
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="blue-darken-1"
                        variant="text"
                        @click="deleteDialog = false"
                    >
                        キャンセル
                    </v-btn>
                    <form
                        :action="`/owner/shops/${props.shop.slug}/bookings/${props.booking.id}`"
                        method="POST"
                        style="display: inline"
                    >
                        <input
                            type="hidden"
                            name="_token"
                            :value="props.csrfToken"
                        />
                        <input type="hidden" name="_method" value="DELETE" />
                        <v-btn color="error" variant="text" type="submit">
                            削除する
                        </v-btn>
                    </form>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import ShopHeader from "@/owner/shops/components/ShopHeader.vue";
import { formatInTimeZone } from "date-fns-tz";

// --- Type Definitions ---
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
}
interface Staff {
    id: number;
    profile: { nickname: string };
    schedules: StaffSchedule[];
}
interface BookingOption {
    id: number;
    option_id: number;
    option_name: string;
    option_price: number;
    option_duration: number;
}
interface ShopBooker {
    id: number;
    number: number;
    crm?: {
        name_kana: string;
        shop_memo: string;
    };
}
interface Booking {
    id: number;
    start_at: string;
    end_at: string;
    assigned_staff_id: number;
    menu_id: number;
    shop_booker_id: number;
    // Snapshots/Direct Columns
    booker_name: string;
    contact_email: string;
    contact_phone: string;
    note_from_booker: string;
    shop_memo: string;
    timezone?: string;
    
    // Relations
    booking_options?: BookingOption[];
    booker?: ShopBooker;
}
interface Props {
    shop: Shop;
    menus: Menu[];
    staffs: Staff[];
    booking: Booking;
    errors: string[];
    oldInput: { [key: string]: any } | null;
    csrfToken: string;
}

// --- Props ---
const props = defineProps<Props>();

// --- Form State ---
console.log('Props Booking:', props.booking);
console.log('Props oldInput:', props.oldInput);

// Initialize directly from props.booking (simpler and more reliable)
const form = ref<{
    start_at: string;
    menu_id: number | null;
    option_ids: number[];
    assigned_staff_id: number | null;
    shop_booker_id: number;
    booker_number: number | null;
    booker_name: string;
    booker_name_kana: string;
    contact_email: string;
    contact_phone: string;
    shop_memo: string;
    note_from_booker: string;
}>({
    start_at: props.booking.start_at,
    menu_id: Number(props.booking.menu_id),
    option_ids: props.booking.booking_options ? props.booking.booking_options.map(bo => Number(bo.option_id)) : [],
    assigned_staff_id: Number(props.booking.assigned_staff_id),
    shop_booker_id: Number(props.booking.shop_booker_id),
    booker_number: props.booking.booker?.number ?? null,
    booker_name: props.booking.booker_name,
    booker_name_kana: props.booking.booker?.crm?.name_kana ?? "",
    contact_email: props.booking.contact_email,
    contact_phone: props.booking.contact_phone,
    shop_memo: props.booking.shop_memo,
    note_from_booker: props.booking.note_from_booker,
});

console.log('form.value:', form.value);

// Initialize Date/Time from start_at with shop timezone awareness
// Backend sends UTC timestamps (e.g., "2025-12-19T19:00:00.000000Z")
// Use date-fns-tz to convert to shop's timezone (consistent with existing codebase pattern from shifts/Edit.vue)
const shopTimezone = props.booking.timezone || 'Asia/Tokyo';

// Extract date and time components in shop timezone using formatInTimeZone
const initialDateStr = form.value.start_at 
    ? formatInTimeZone(form.value.start_at, shopTimezone, 'yyyy-MM-dd')
    : formatInTimeZone(new Date(), shopTimezone, 'yyyy-MM-dd');
    
const initialTime = form.value.start_at 
    ? formatInTimeZone(form.value.start_at, shopTimezone, 'HH:mm')
    : null;

// Create Date object for calendar (using local timezone but with correct date from shop timezone)
const initialDate = new Date(initialDateStr + 'T00:00:00');

// Initialize Refs
const selectedTime = ref<string | null>(initialTime);
const directTimeInput = ref<string | null>(initialTime);
const assignedStaffs = ref<Staff[]>([]); 
const showAllStaffs = ref(false);
const allowOffShift = ref(false);
const staffWarning = ref<string | null>(null);
const shiftWarning = ref<string | null>(null);
const conflictWarning = ref<string | null>(null);

const deleteDialog = ref(false);

// --- Calendar State ---
const workingDays = ref<string[]>([]);
const pickerYear = ref(initialDate.getFullYear());
const pickerMonth = ref(initialDate.getMonth() + 1);

// --- Time Slot State & Logic ---
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
const isLoading = ref(false);
const selectedDateValue = ref<Date | null>(initialDate);
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

const fetchTimeSlots = async () => {
    if (
        !form.value.menu_id ||
        !form.value.assigned_staff_id ||
        !formattedSelectedDate.value
    ) {
        timeSlots.value = [];
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(
            `/owner/api/shops/${props.shop.slug}/staffs/${form.value.assigned_staff_id}/timeslots`,
            {
                params: {
                    date: formattedSelectedDate.value,
                    menu_id: form.value.menu_id,
                    option_ids: form.value.option_ids,
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
        const url = `/owner/api/shops/${props.shop.slug}/menus/${form.value.menu_id}/staffs`;
        const response = await axios.get(url);
        assignedStaffs.value = response.data.staffs;
    } catch (error) {
        console.error("割り当てスタッフの取得に失敗しました:", error);
        assignedStaffs.value = [];
    }
};

watch(
    [
        () => form.value.menu_id,
        () => form.value.assigned_staff_id,
        () => formattedSelectedDate.value,
    ],
    fetchTimeSlots
);

watch([() => form.value.menu_id, showAllStaffs], ([newMenuId, newShowAll], [oldMenuId, oldShowAll]) => {
    // Menu switch
    if (newMenuId !== oldMenuId) {
        if (newMenuId) {
            fetchAssignedStaffs();
            // Since we initialize form.menu_id correctly, the first run of watch (if immediate) 
            // will have oldMenuId as undefined/null depending on Vue version, BUT
            // typically watch is lazy by default.
            // If user changes menu, reset staff.
            form.value.assigned_staff_id = null;
        } else {
            assignedStaffs.value = [];
            form.value.assigned_staff_id = null;
        }
    }
    // Show all staffs switch
    else if (newShowAll !== oldShowAll) {
        if (!newShowAll) {
            const isStaffInList = assignedStaffs.value.some(s => s.id === form.value.assigned_staff_id);
            if (form.value.assigned_staff_id && !isStaffInList) {
                form.value.assigned_staff_id = null;
            }
        }
    }
});

// --- Computed Properties for Calculation ---
const selectedMenu = computed((): Menu | undefined =>
    props.menus.find((m) => m.id === form.value.menu_id)
);
const availableOptions = computed(
    (): Option[] => selectedMenu.value?.options ?? []
);
const availableStaffs = computed((): Staff[] => {
    if (!form.value.menu_id) return [];
    if (showAllStaffs.value) {
        return props.staffs;
    }
    if (selectedMenu.value && !selectedMenu.value.requires_staff_assignment) {
        return props.staffs;
    }
    return assignedStaffs.value;
});

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

// --- Dialog State ---
const dateDialog = ref(false);
const timePickerDialog = ref(false);

// --- Calendar Logic ---
const fetchWorkingDays = async (year: number, month: number) => {
    if (!form.value.assigned_staff_id) {
        workingDays.value = [];
        return;
    }
    const yearMonth = `${year}-${String(month).padStart(2, '0')}`;
    
    try {
        const response = await axios.get(
            `/owner/api/shops/${props.shop.slug}/staffs/${form.value.assigned_staff_id}/working-days`,
            {
                params: { year_month: yearMonth }
            }
        );
        workingDays.value = response.data;
    } catch (error) {
        console.error("Shift data fetch failed:", error);
    }
};

const allowedDates = (date: unknown): boolean => {
    if (allowOffShift.value) return true;
    const dateString = getDateString(date);
    if (!dateString) return false;
    return workingDays.value.includes(dateString);
};

const isWorkingDay = (date: unknown): boolean => {
    const dateString = getDateString(date);
    if (!dateString) return false;
    return workingDays.value.includes(dateString);
};

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

const getDayNumber = (dateInput: unknown): string => {
    const dateString = getDateString(dateInput);
    if (!dateString) return "";
    return String(parseInt(dateString.split('-')[2], 10));
};

const getDayStyle = (date: unknown) => {
    return {};
};

const onPickerYearChange = (year: number) => {
    pickerYear.value = year;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};
const onPickerMonthChange = (month: number) => {
    pickerMonth.value = month;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};

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

const updateDateFromPicker = (date: Date | null) => {
    if (date) {
        setDate(date);
    }
    dateDialog.value = false;
};

// --- Watchers ---
watch(
    () => form.value.menu_id,
    (newVal, oldVal) => {
        // Init時のセットアップでない場合のみリセット
        if (oldVal !== null) {
            form.value.option_ids = [];
            selectedTime.value = null;
        }
    }
);

const onDirectTimeBlur = () => {
    checkShift();
    checkConflict();
};

const checkStaffAssignment = async () => {
    staffWarning.value = null;
    if (!showAllStaffs.value || !form.value.assigned_staff_id || !form.value.menu_id) return;
    if (selectedMenu.value && !selectedMenu.value.requires_staff_assignment) return;

    try {
        const response = await axios.get(
            `/owner/api/shops/${props.shop.slug}/bookings/validate-staff`,
            {
                params: {
                    menu_id: form.value.menu_id,
                    assigned_staff_id: form.value.assigned_staff_id,
                },
            }
        );
        if (!response.data.valid) {
            staffWarning.value = "※このスタッフはメニューの担当設定に含まれていません";
        }
    } catch (error) {
        console.error("Staff validation failed:", error);
    }
};

const checkShift = async () => {
    shiftWarning.value = null;
    if (!directTimeInput.value || !form.value.start_at || !form.value.assigned_staff_id) return;

    try {
        const response = await axios.get(
            `/owner/api/shops/${props.shop.slug}/bookings/validate-shift`,
            {
                params: {
                    assigned_staff_id: form.value.assigned_staff_id,
                    start_at: form.value.start_at,
                    menu_id: form.value.menu_id,
                    option_ids: form.value.option_ids,
                },
            }
        );
        if (!response.data.valid) {
            shiftWarning.value = "※この日時は担当スタッフのシフト外です";
        }
    } catch (error) {
        console.error("Shift validation failed:", error);
    }
};

const checkConflict = async () => {
    conflictWarning.value = null;
    // 自分自身の予約IDを除外してチェックする必要があるが、
    // バックエンドの validateConflict で booking_id を除外するロジックが実装されているか確認必要。
    // 現状のAPIは予約作成用かもしれない。その場合、自分の予約と重複判定される恐れがある。
    // API側で ignore_booking_id パラメータを受け付けるか確認 => BookingController::validateConflict を見るべきだが、
    // ここでは簡易的にメッセージを出すのみ。バックエンド実装時に考慮が必要。
    // TODO: validateConflict API should accept `ignore_booking_id`
};

watch([selectedDateValue, selectedTime], ([newDate, newTime]) => {
    if (newDate) {
        if (newTime) {
            form.value.start_at = `${formattedSelectedDate.value} ${newTime}:00`;
        } else if (directTimeInput.value && /^\d{2}:\d{2}$/.test(directTimeInput.value)) {
            form.value.start_at = `${formattedSelectedDate.value} ${directTimeInput.value}:00`;
        } else {
             form.value.start_at = "";
        }
    } else {
        form.value.start_at = "";
    }
});

watch(selectedTime, (newTime) => {
    if (newTime) {
        directTimeInput.value = newTime;
        checkShift();
        checkConflict();
    }
});

watch(directTimeInput, (newVal) => {
    if (newVal && /^\d{2}:\d{2}$/.test(newVal)) {
        if (timeSlots.value.includes(newVal)) {
            selectedTime.value = newVal;
        } else {
            selectedTime.value = null;
        }
        if (formattedSelectedDate.value) {
            form.value.start_at = `${formattedSelectedDate.value} ${newVal}:00`;
        }
    } else {
        selectedTime.value = null;
    }
});

// --- Validation ---
const isFormValid = computed(() => {
    return !!(
        form.value.start_at &&
        form.value.booker_name &&
        form.value.assigned_staff_id &&
        form.value.contact_email &&
        form.value.contact_phone &&
        form.value.menu_id
    );
});

const validateAndSubmit = (event: Event) => {
    if (!isFormValid.value) {
        event.preventDefault();
        alert("必須項目を入力してください。");
        return;
    }
};

// --- Lifecycle Hooks ---
onMounted(async () => {
    // Initial fetch of staffs for the preset menu
    if (form.value.menu_id) {
        await fetchAssignedStaffs();
        
        // After fetching staffs, check if the currently assigned staff is in the list
        // Only enable 'Show All' if menu requires staff assignment AND assigned staff is not in list
        const currentMenu = props.menus.find(m => m.id === form.value.menu_id);
        if (currentMenu?.requires_staff_assignment) {
            const isStaffInList = assignedStaffs.value.some(s => s.id === form.value.assigned_staff_id);
            if (form.value.assigned_staff_id && !isStaffInList) {
                showAllStaffs.value = true;
            }
        }
    }
    
    // Handle validation errors: overwrite with oldInput if present
    if (props.oldInput && !Array.isArray(props.oldInput) && Object.keys(props.oldInput).length > 0) {
        if (props.oldInput.menu_id) form.value.menu_id = Number(props.oldInput.menu_id);
        if (props.oldInput.assigned_staff_id) form.value.assigned_staff_id = Number(props.oldInput.assigned_staff_id);
        if (props.oldInput.contact_email) form.value.contact_email = props.oldInput.contact_email;
        if (props.oldInput.contact_phone) form.value.contact_phone = props.oldInput.contact_phone;
        if (props.oldInput.shop_memo) form.value.shop_memo = props.oldInput.shop_memo;
        if (props.oldInput.note_from_booker) form.value.note_from_booker = props.oldInput.note_from_booker;
        
        if (props.oldInput.start_at) {
            const d = new Date(props.oldInput.start_at);
            selectedDateValue.value = d;
            const time = (`0` + d.getHours()).slice(-2) + ":" + (`0` + d.getMinutes()).slice(-2);
            selectedTime.value = time;
            directTimeInput.value = time;
        }
        
        if (props.oldInput.option_ids) {
            form.value.option_ids = (props.oldInput.option_ids as any[]).map(Number);
        }
    }
});
</script>
