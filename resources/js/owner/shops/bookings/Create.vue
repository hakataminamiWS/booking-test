<template>
    <v-container>
        <!-- Navigation -->
        <v-row>
            <v-col cols="12">
                <v-btn
                       :href="`/owner/shops/${props.shop.slug}/bookings`"
                       prepend-icon="mdi-arrow-left"
                       variant="text">
                    予約一覧に戻る
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
                    <v-card-title>手動予約登録</v-card-title>
                    <v-card-text>
                        <form
                              :action="`/owner/shops/${props.shop.slug}/bookings`"
                              method="POST">
                            <input
                                   type="hidden"
                                   name="_token"
                                   :value="props.csrfToken" />
                            <input
                                   type="hidden"
                                   name="start_at"
                                   :value="form.start_at" />
                            <input
                                   type="hidden"
                                   name="shop_booker_id"
                                   :value="form.shop_booker_id ?? ''" />

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

                            <!-- Booker Selection -->
                            <p class="text-subtitle-1 font-weight-bold mt-4">
                                予約者
                            </p>
                            <v-text-field
                                          v-model="form.booker_name"
                                          name="booker_name"
                                          label="予約者名 *"
                                          :readonly="!!form.shop_booker_id"
                                          required
                                          placeholder="予約者名を入力">
                                <template v-slot:append-inner>
                                    <v-btn
                                           color="primary"
                                           size="small"
                                           variant="text"
                                           @click="bookerDialog = true">
                                        選択 / 新規
                                    </v-btn>
                                </template>
                            </v-text-field>
                            <v-text-field v-model="form.booker_name_kana" name="booker_name_kana"
                                          label="予約者のよみがな（予約者には表示されません）"
                                          :readonly="!!form.shop_booker_id"></v-text-field>
                            <v-text-field v-model="form.contact_email" name="contact_email" label="連絡先メールアドレス *"
                                          type="email"
                                          required></v-text-field>
                            <v-text-field v-model="form.contact_phone" name="contact_phone" label="連絡先電話番号 *" type="tel"
                                          required></v-text-field>
                            <v-textarea v-model="form.shop_memo" name="shop_memo" label="店舗側のメモ（予約者には表示されません）"
                                        rows="3"></v-textarea>
                            <v-divider class="my-6"></v-divider>

                            <!-- Menu & Options -->
                            <p class="text-subtitle-1 font-weight-bold">
                                メニュー・オプション
                            </p>
                            <v-select v-model="form.menu_id" name="menu_id" :items="props.menus" item-title="name"
                                      item-value="id" label="メニュー *"
                                      required class="mb-2"></v-select>
                            <v-select v-model="form.option_ids" :items="availableOptions" item-title="name"
                                      item-value="id" label="オプション" multiple
                                      chips closable-chips :disabled="!form.menu_id" class="mb-2"></v-select>
                            <!-- 配列送信用の隠しフィールド -->
                            <input v-for="optId in form.option_ids" :key="optId" type="hidden" name="option_ids[]"
                                   :value="optId" />
                            <p class="text-subtitle-1">
                                合計: {{ totalDuration }}分 /
                                {{ totalPrice.toLocaleString() }}円
                            </p>
                            <v-divider class="my-6"></v-divider>

                            <!-- Staff Selection -->
                            <p class="text-subtitle-1 font-weight-bold">
                                担当スタッフ
                            </p>
                            <v-select v-model="form.assigned_staff_id" name="assigned_staff_id" :items="availableStaffs"
                                      item-title="profile.nickname" item-value="id" label="担当スタッフ *"
                                      :disabled="!form.menu_id"
                                      @update:focused="!$event && checkStaffAssignment()"></v-select>
                            <v-alert v-if="staffWarning" type="warning" density="compact" variant="tonal"
                                     class="mb-2 mt-2">
                                {{ staffWarning }}
                            </v-alert>
                            <v-checkbox v-model="showAllStaffs" label="メニューに割り当たっていない担当スタッフも表示する"
                                        :disabled="!form.menu_id" density="compact"
                                        class="mt-n4"></v-checkbox>
                            <v-divider class="my-6"></v-divider>

                            <!-- Date & Time Selection -->
                            <p class="text-subtitle-1 font-weight-bold">
                                予約日時
                            </p>
                            <v-text-field v-model="formattedSelectedDate" label="予約日付 *"
                                          @click:append-inner="dateDialog = true"
                                          append-inner-icon="mdi-calendar" readonly></v-text-field>
                            <v-checkbox v-model="allowOffShift" label="担当スタッフのシフト外も選択可能にする" density="compact"
                                        class="mt-n4"></v-checkbox>

                            <p class="text-caption mt-4">予約時間 *</p>
                            <v-sheet class="pa-2" border rounded min-height="68">
                                <!-- ローディング表示 -->
                                <div v-if="isLoading" class="d-flex justify-center align-center fill-height">
                                    <v-progress-circular indeterminate color="primary"></v-progress-circular>
                                </div>

                                <!-- タイムチップ表示 (時間ごとにグループ化) -->
                                <!-- タイムチップ表示 (時間ごとにグループ化) -->
                                <div v-else-if="
                                    groupedTimeSlots.length > 0
                                ">
                                    <div v-for="group in groupedTimeSlots" :key="group.hour"
                                         class="d-flex align-center py-1"
                                         style="border-bottom: 1px solid #eee">
                                        <div class="text-body-2 font-weight-bold mr-4" style="width: 40px">
                                            {{ group.hour }}時
                                        </div>
                                        <v-chip-group v-model="selectedTime" column mandatory active-class="primary">
                                            <v-chip v-for="time in group.slots" :key="time" :value="time"
                                                    variant="outlined" size="small">
                                                {{ time }}
                                            </v-chip>
                                        </v-chip-group>
                                    </div>
                                </div>

                                <!-- 予約枠がない場合の表示 -->
                                <div v-else class="d-flex justify-center align-center fill-height text-grey-darken-1">
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

                            <v-text-field v-model="directTimeInput" label="直接入力（シフト外の入力も可能です）" placeholder="HH:MM"
                                          append-inner-icon="mdi-clock-outline" class="mt-4" style="max-width: 200px"
                                          @click:append-inner="timePickerDialog = true"
                                          @blur="onDirectTimeBlur"></v-text-field>
                            <v-alert v-if="shiftWarning" type="warning" density="compact" variant="tonal" class="mb-2">
                                {{ shiftWarning }}
                            </v-alert>
                            <v-alert v-if="conflictWarning" type="error" density="compact" variant="tonal" class="mb-2">
                                {{ conflictWarning }}
                            </v-alert>
                            <v-divider class="my-6"></v-divider>

                            <!-- Memo -->
                            <p class="text-subtitle-1 font-weight-bold">
                                予約時メモ
                            </p>
                            <v-textarea v-model="form.note_from_booker" name="note_from_booker" label="予約に関するメモ"
                                        rows="3"></v-textarea>

                            <!-- Actions -->
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn type="submit" color="primary" :disabled="!form.start_at || !form.booker_name || !form.assigned_staff_id
                                    ">登録する</v-btn>
                            </v-card-actions>
                        </form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Booker Selection Dialog -->
        <v-dialog v-model="bookerDialog" max-width="800px" persistent>
            <v-card>
                <v-tabs v-model="dialogTab" bg-color="primary">
                    <v-tab value="select">既存顧客から選択</v-tab>
                    <v-tab value="create">新しく顧客を登録</v-tab>
                </v-tabs>
                <v-card-text>
                    <v-window v-model="dialogTab">
                        <v-window-item value="select">
                            <v-text-field v-model="bookerSearchQuery" label="顧客名、連絡先で検索"
                                          prepend-inner-icon="mdi-magnify"
                                          variant="solo-filled" flat hide-details class="mb-4"></v-text-field>
                            <v-list lines="two" style="max-height: 400px; overflow-y: auto">
                                <v-list-item v-for="booker in filteredBookers" :key="booker.id" :title="booker.name"
                                             :subtitle="`${booker.contact_email || 'メール未登録'
                                                } / ${booker.contact_phone || '電話番号未登録'
                                                }`" :active="selectedBookerInDialog === booker.id
                                                    " @click="selectedBookerInDialog = booker.id">
                                    <template v-slot:prepend>
                                        <v-avatar color="grey-lighten-1">
                                            <v-icon color="white">mdi-account</v-icon>
                                        </v-avatar>
                                    </template>
                                </v-list-item>
                            </v-list>
                        </v-window-item>
                        <v-window-item value="create">
                            <v-container>
                                <v-row>
                                    <v-col cols="12"><v-text-field v-model="newBookerForm.nickname" label="予約者名 *"
                                                      required
                                                      :rules="[
                                                        (v) =>
                                                            !!v || '予約者名は必須です',
                                                    ]"></v-text-field></v-col>
                                    <v-col cols="12"><v-text-field v-model="newBookerForm.booker_name_kana
                                        " label="予約者のよみがな"></v-text-field></v-col>
                                    <v-col cols="12"><v-text-field v-model="newBookerForm.contact_email
                                        " label="連絡先メールアドレス *" type="email" required></v-text-field></v-col>
                                    <v-col cols="12"><v-text-field v-model="newBookerForm.contact_phone
                                        " label="連絡先電話番号 *" type="tel" required></v-text-field></v-col>
                                    <v-col cols="12"><v-textarea v-model="newBookerForm.shop_memo" label="店舗側のメモ"
                                                    rows="3"></v-textarea></v-col>
                                </v-row>
                            </v-container>
                        </v-window-item>
                    </v-window>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="cancelBookerSelection">キャンセル</v-btn>
                    <v-btn color="primary" @click="confirmBookerSelection">決定</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Date Selection Dialog -->
        <v-dialog v-model="dateDialog" max-width="320px">
            <v-date-picker v-model="selectedDateValue" @update:model-value="updateDateFromPicker"
                           @update:year="onPickerYearChange" @update:month="onPickerMonthChange"
                           :allowed-dates="allowedDates"
                           show-adjacent-months>
                <!-- Custom Day Slot for Dots -->
                <template v-slot:day="{ item, props: dayProps }">
                    <v-btn
                           v-bind="dayProps"
                           :style="getDayStyle(item)"
                           class="d-flex justify-center align-center"
                           style="position: relative;"
                           variant="text"
                           size="small"
                           rounded="circle">
                        <!-- 日付の数字 (Vuetifyのデフォルト表示を維持しつつ、ドットを追加したいが完全置換になるため数字も描画) -->
                        <!-- Note: dayProps contains onClick, class, etc. -->
                        {{ getDayNumber(item) }}

                        <!-- Dot for working day -->
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
                            "></div>
                    </v-btn>
                </template>
            </v-date-picker>
        </v-dialog>

        <!-- Time Picker Dialog -->
        <v-dialog v-model="timePickerDialog" width="auto">
            <v-card>
                <v-time-picker v-model="directTimeInput" format="24hr"></v-time-picker>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" variant="text" @click="timePickerDialog = false">
                        完了
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import ShopHeader from "@/components/common/ShopHeader.vue";

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
    profile: { nickname: string };
    schedules: StaffSchedule[];
}
interface Booker {
    id: number;
    name: string;
    contact_email: string;
    contact_phone: string;
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
}
interface Props {
    shop: Shop;
    menus: Menu[];
    staffs: Staff[];
    bookers: Booker[];
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
    shop_booker_id: null as number | null,
    booker_name: "",
    booker_name_kana: "",
    contact_email: "",
    contact_phone: "",
    shop_memo: "",
    note_from_booker: "",
});
const selectedTime = ref<string | null>(null);
const directTimeInput = ref<string | null>(null);
const assignedStaffs = ref<Staff[]>([]); // APIから取得したメニューに割り当てられているスタッフを保持
const showAllStaffs = ref(false);
const allowOffShift = ref(false);
const staffWarning = ref<string | null>(null);
const shiftWarning = ref<string | null>(null);

const conflictWarning = ref<string | null>(null);



// --- Calendar State ---
const workingDays = ref<string[]>([]); // YYYY-MM-DD strings
const pickerYear = ref(new Date().getFullYear());
const pickerMonth = ref(new Date().getMonth() + 1);

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

    // 時間順 (00 -> 23) にソートして配列で返す
    return Object.keys(groups).sort((a, b) => Number(a) - Number(b)).map(hour => ({
        hour,
        slots: groups[hour]
    }));
});
const isLoading = ref(false);
const selectedDateValue = ref<Date | null>(new Date());
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
                    // Note: `totalDuration` is calculated on the backend based on menu/options
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

watch([() => form.value.menu_id, showAllStaffs], () => {
    if (form.value.menu_id) {
        // メニューが選択されている場合
        if (showAllStaffs.value) {
            // チェックが入っている場合は全スタッフ (props.staffs) を使うのでAPI呼び出しは不要
            form.value.assigned_staff_id = null;
        } else if (selectedMenu.value && !selectedMenu.value.requires_staff_assignment) {
            // スタッフ割り当てが必須でない場合は全スタッフ (props.staffs) を使うのでAPI呼び出しは不要
            form.value.assigned_staff_id = null;
        } else {
            // チェックがなく、かつスタッフ割り当てが必須の場合はAPIから取得する
            fetchAssignedStaffs();
            form.value.assigned_staff_id = null;
        }
    } else {
        // メニューが未選択の場合はスタッフリストをクリア
        assignedStaffs.value = [];
        form.value.assigned_staff_id = null;
    }
});

// --- Timezone & Input Sync Logic (Simplified) ---
const shopTimezone = computed(() => props.shop.timezone || 'Asia/Tokyo');

watch(selectedTime, (newVal) => {
    if (newVal) {
        directTimeInput.value = newVal;
    }
});

watch(
    [() => formattedSelectedDate.value, directTimeInput],
    ([date, time]) => {
        if (date && time) {
            form.value.start_at = `${date} ${time}`;
        } else {
            form.value.start_at = "";
        }
    }
);

// --- Computed Properties for Calculation ---
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
const bookerDialog = ref(false);
const dialogTab = ref("select");
const bookerSearchQuery = ref("");
const selectedBookerInDialog = ref<number | null>(null);
const newBookerForm = ref({
    nickname: "",
    booker_name_kana: "",
    contact_email: "",
    contact_phone: "",
    shop_memo: "",
});
const dateDialog = ref(false);

const timePickerDialog = ref(false);

// --- Calendar Logic ---
const fetchWorkingDays = async (year: number, month: number) => {
    if (!form.value.assigned_staff_id) {
        workingDays.value = [];
        return;
    }

    // 既に取得済みの月などをキャッシュする実装も可能だが、
    // シンプルに月が変わるたびにリクエストする
    const yearMonth = `${year}-${String(month).padStart(2, '0')}`;

    try {
        const response = await axios.get(
            `/owner/api/shops/${props.shop.slug}/staffs/${form.value.assigned_staff_id}/working-days`,
            {
                params: { year_month: yearMonth }
            }
        );
        // 配列を結合するのではなく、その月を含む閲覧範囲のデータとして保持する形にする
        // (複数月保持したい場合はSetなどで管理)
        // ここではシンプルに「表示中の月」のデータを保持することにする
        // ただし、月をまたぐナビゲーションの際に前のデータが消えると
        // トランジション中にドットが消える可能性があるため、
        // 実際には追加していくのがベターだが、要件としては「表示月」で十分。
        // 今回はとりあえず取得した結果で上書きする（ナビゲーション後に再取得）
        workingDays.value = response.data;
    } catch (error) {
        console.error("Shift data fetch failed:", error);
    }
};

const allowedDates = (date: unknown): boolean => {
    // allowOffShift = true なら全日程許可
    if (allowOffShift.value) return true;

    const dateString = getDateString(date);
    if (!dateString) return false;

    // workingDays に含まれているかどうか
    return workingDays.value.includes(dateString);
};

const isWorkingDay = (date: unknown): boolean => {
    const dateString = getDateString(date);
    if (!dateString) return false;
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
        // オブジェクトの場合、一般的に 'value', 'date', 'iso' などのプロパティを持つ可能性がある
        // Vuetify の内部実装やアダプタによっては構造が異なるが、
        // 'value' が Dateオブジェクトか文字列であることが多い
        const val = (dateInput as any).value || (dateInput as any).date;
        if (val) {
            if (val instanceof Date) d = val;
            else d = new Date(val);
        } else {
            // プロパティが見つからない場合、dateInputそのものがDateのように振る舞うか試す
            // JSON stringifyなどで確認できないため、ひとまず toString() が日付っぽいかなど...
            // しかし new Date(object) は NaN になるので、
            // ここでは null を返す（または今日を返すなどエラー回避）
            return null;
        }
    }

    if (!d || isNaN(d.getTime())) return null;

    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Helper: 日付の数字を取得
const getDayNumber = (dateInput: unknown): string => {
    const dateString = getDateString(dateInput);
    if (!dateString) return "";
    return String(parseInt(dateString.split('-')[2], 10)); // leading zero removal
};

const getDayStyle = (date: unknown) => {
    // 独自のスタイルを適用したい場合に使用
    return {};
};

// Picker navigation handlers
const onPickerYearChange = (year: number) => {
    pickerYear.value = year;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};
const onPickerMonthChange = (month: number) => {
    pickerMonth.value = month;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};

// Watchers for calendar data
watch(() => form.value.assigned_staff_id, () => {
    // スタッフが変わったら再取得
    if (form.value.assigned_staff_id) {
        fetchWorkingDays(pickerYear.value, pickerMonth.value);
    } else {
        workingDays.value = [];
    }
});

watch(dateDialog, (isOpen) => {
    if (isOpen && form.value.assigned_staff_id) {
        // ダイアログが開いたときに初期データの確認（まだなければ取得）
        // selectedDateValue から year/month をセット
        if (selectedDateValue.value) {
            const d = new Date(selectedDateValue.value);
            pickerYear.value = d.getFullYear();
            pickerMonth.value = d.getMonth() + 1;
        }
        fetchWorkingDays(pickerYear.value, pickerMonth.value);
    }
});

// --- Computed Properties for UI ---
const selectedMenu = computed((): Menu | undefined =>
    props.menus.find((m) => m.id === form.value.menu_id)
);
const availableOptions = computed(
    (): Option[] => selectedMenu.value?.options ?? []
);
const availableStaffs = computed((): Staff[] => {
    if (!form.value.menu_id) return []; // メニューが選択されていない場合は空
    if (showAllStaffs.value) {
        return props.staffs; // チェックが入っている場合は全スタッフ
    }
    // スタッフ割り当てが不要なメニューの場合は全スタッフを表示
    if (selectedMenu.value && !selectedMenu.value.requires_staff_assignment) {
        return props.staffs;
    }
    return assignedStaffs.value; // チェックがない場合は割り当てスタッフ (APIから取得)
});

const selectedBookerName = computed((): string => {
    if (!form.value.shop_booker_id && form.value.booker_name)
        return form.value.booker_name;
    if (form.value.shop_booker_id) {
        const booker = props.bookers.find(
            (b) => b.id === form.value.shop_booker_id
        );
        return booker?.name ?? "選択されていません";
    }
    return "選択されていません";
});

const filteredBookers = computed((): Booker[] => {
    if (!bookerSearchQuery.value) return props.bookers;
    const query = bookerSearchQuery.value.toLowerCase();
    return props.bookers.filter(
        (booker) =>
            booker.name.toLowerCase().includes(query) ||
            (booker.contact_email &&
                booker.contact_email.toLowerCase().includes(query)) ||
            (booker.contact_phone && booker.contact_phone.includes(query))
    );
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
    () => {
        form.value.option_ids = [];
        selectedTime.value = null; // Reset time when menu changes
    }
);

watch(
    () => form.value.shop_booker_id,
    (newBookerId) => {
        if (newBookerId) {
            const booker = props.bookers.find((b) => b.id === newBookerId);
            if (booker) {
                form.value.booker_name = booker.name;
                form.value.booker_name_kana = booker.crm?.name_kana ?? "";
                form.value.contact_email = booker.contact_email;
                form.value.contact_phone = booker.contact_phone;
                form.value.shop_memo = booker.crm?.shop_memo ?? "";
            }
        } else {
            if (dialogTab.value !== "create") {
                form.value.booker_name = "";
                form.value.booker_name_kana = "";
                form.value.contact_email = "";
                form.value.contact_phone = "";
                form.value.shop_memo = "";
            }
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

    // メニューがスタッフ割り当て必須でない場合はチェック不要
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
    if (!directTimeInput.value || !form.value.start_at || !form.value.assigned_staff_id) return;

    try {
        const response = await axios.get(
            `/owner/api/shops/${props.shop.slug}/bookings/validate-conflict`,
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
            conflictWarning.value = "※この時間帯には既に別の予約が入っています（重複登録になります）";
        }
    } catch (error) {
        console.error("Conflict validation failed:", error);
    }
};

watch([selectedDateValue, selectedTime], ([newDate, newTime]) => {
    if (newDate) {
        if (newTime) {
            form.value.start_at = `${formattedSelectedDate.value} ${newTime}:00`;
        } else if (directTimeInput.value && /^\d{2}:\d{2}$/.test(directTimeInput.value)) {
            // selectedTime is null (e.g. shift outside), but direct input exists
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
        // Chip選択時は有効な枠なので、警告チェックを走らせて正当な状態（警告なし）にする
        // また start_at の更新もここで行われる（selectedTimeのwatchが別途あるため）

        // start_atの更新ロジックは既存の watch([selectedDateValue, selectedTime]...) で行われるので不要だが、
        // directTimeInputの更新に伴うバリデーションリセット等のために onDirectTimeBlur 相当を呼ぶか、
        // あるいはバリデーション関数を直接呼ぶ。
        // ここではUIの同期が主目的なので値をセットする。
        // バリデーション状態を最新にするためチェックのみ走らせる。
        checkShift();
        checkConflict();
    }
});

watch(directTimeInput, (newVal) => {
    if (newVal && /^\d{2}:\d{2}$/.test(newVal)) {
        // 入力された値がタイムスロット一覧にある場合はそのChipを選択
        if (timeSlots.value.includes(newVal)) {
            selectedTime.value = newVal;
        } else {
            // ない場合はChipの選択を解除
            selectedTime.value = null;
        }

        // start_at の更新
        if (formattedSelectedDate.value) {
            form.value.start_at = `${formattedSelectedDate.value} ${newVal}:00`;
        }
    } else {
        // 入力が空などの場合
        selectedTime.value = null;
    }
});



// --- Dialog Methods ---
function confirmBookerSelection() {
    if (dialogTab.value === "select") {
        if (selectedBookerInDialog.value) {
            form.value.shop_booker_id = selectedBookerInDialog.value;
        }
    } else if (dialogTab.value === "create") {
        if (newBookerForm.value.nickname) {
            form.value.shop_booker_id = null;
            form.value.booker_name = newBookerForm.value.nickname;
            form.value.booker_name_kana = newBookerForm.value.booker_name_kana;
            form.value.contact_email = newBookerForm.value.contact_email;
            form.value.contact_phone = newBookerForm.value.contact_phone;
            form.value.shop_memo = newBookerForm.value.shop_memo;
        } else {
            alert("予約者名は必須です。");
            return;
        }
    }
    bookerDialog.value = false;
}

function cancelBookerSelection() {
    selectedBookerInDialog.value = form.value.shop_booker_id;
    newBookerForm.value = {
        nickname: "",
        booker_name_kana: "",
        contact_email: "",
        contact_phone: "",
        shop_memo: "",
    };
    bookerDialog.value = false;
}

watch(bookerDialog, (isOpen) => {
    if (isOpen) {
        selectedBookerInDialog.value = form.value.shop_booker_id;
        newBookerForm.value = {
            nickname: "",
            booker_name_kana: "",
            contact_email: "",
            contact_phone: "",
            shop_memo: "",
        };
        // 常に 'select' タブをデフォルトにする (ユーザー要望)
        dialogTab.value = "select";
    }
});

// --- Lifecycle Hooks ---
onMounted(() => {
    setDate(new Date()); // Set today as initial date

    if (props.oldInput) {
        form.value.menu_id = props.oldInput.menu_id
            ? Number(props.oldInput.menu_id)
            : null;
        form.value.option_ids = (props.oldInput.option_ids ?? []).map(Number);
        form.value.assigned_staff_id = props.oldInput.assigned_staff_id
            ? Number(props.oldInput.assigned_staff_id)
            : null;
        form.value.shop_booker_id = props.oldInput.shop_booker_id
            ? Number(props.oldInput.shop_booker_id)
            : null;
        form.value.booker_name = props.oldInput.booker_name ?? "";
        form.value.booker_name_kana = props.oldInput.booker_name_kana ?? "";
        form.value.contact_email = props.oldInput.contact_email ?? "";
        form.value.contact_phone = props.oldInput.contact_phone ?? "";
        form.value.shop_memo = props.oldInput.shop_memo ?? "";
        form.value.note_from_booker = props.oldInput.note_from_booker ?? "";

        if (props.oldInput.start_at) {
            const d = new Date(props.oldInput.start_at);
            setDate(d);
            const time =
                (`0` + d.getHours()).slice(-2) +
                ":" +
                (`0` + d.getMinutes()).slice(-2);
            selectedTime.value = time;
            directTimeInput.value = time;
        }
    }
});
</script>
