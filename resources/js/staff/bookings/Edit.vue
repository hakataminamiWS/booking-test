<template>
    <v-app>
        <v-main>
            <v-container fluid class="container-width-1200">
                <!-- Navigation -->
                <v-row>
                    <v-col cols="12">
                        <v-btn
                               :href="`/shops/${props.shop.slug}/staff/bookings`"
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
                        <form
                              :action="`/shops/${props.shop.slug}/staff/bookings/${props.booking.id}`"
                              method="POST">
                            <input
                                   type="hidden"
                                   name="_token"
                                   :value="props.csrfToken" />
                            <input type="hidden" name="_method" value="PUT" />
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

                            <v-row>
                                <!-- Menu/Options & Staff -->
                                <v-col cols="12" md="8">
                                    <v-card variant="text">
                                        <v-card-text class="pa-0">
                                            <v-row>
                                                <v-col cols="12" md="6">
                                                    <v-card variant="text">
                                                        <v-card-title class="px-0">メニュー・オプション</v-card-title>
                                                        <v-card-text class="px-0">
                                                            <v-select v-model="form.menu_id"
                                                                      name="menu_id"
                                                                      :items="props.menus"
                                                                      item-title="name"
                                                                      item-value="id"
                                                                      label="メニュー（必須）"
                                                                      required class="mb-2">
                                                            </v-select>
                                                            <v-select v-model="form.option_ids"
                                                                      hide-details
                                                                      :items="availableOptions"
                                                                      item-title="name"
                                                                      item-value="id"
                                                                      label="オプション"
                                                                      multiple
                                                                      chips
                                                                      closable-chips
                                                                      :disabled="!form.menu_id"
                                                                      class="mb-2">
                                                            </v-select>
                                                            <!-- 配列送信用の隠しフィールド -->
                                                            <input v-for="optId in form.option_ids" :key="optId"
                                                                   type="hidden"
                                                                   name="option_ids[]" :value="optId" />
                                                        </v-card-text>
                                                    </v-card>
                                                </v-col>
                                                <v-col cols="12" md="6">
                                                    <v-card variant="text">
                                                        <v-card-title class="px-0">
                                                            担当スタッフ
                                                        </v-card-title>
                                                        <v-card-text class="px-0" v-if="!form.menu_id">
                                                            <p>
                                                                メニューを選択してください
                                                            </p>
                                                        </v-card-text>

                                                        <v-card-text class="px-0" v-else>
                                                            <v-select v-model="form.assigned_staff_id"
                                                                      name="assigned_staff_id"
                                                                      :items="availableStaffs"
                                                                      item-title="profile.nickname"
                                                                      item-value="id"
                                                                      label="担当スタッフ（必須）"
                                                                      :disabled="!form.menu_id">
                                                                <template v-slot:item="{ item, props }">
                                                                    <v-list-item v-bind="props"
                                                                                 :title="item.raw.profile?.nickname">
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
                                                            <v-checkbox v-model="showAllStaffs"
                                                                        label="メニューに割り当たっていない担当スタッフも表示する" hide-details
                                                                        class="mt-n4">
                                                            </v-checkbox>
                                                        </v-card-text>

                                                    </v-card>

                                                </v-col>
                                            </v-row>
                                        </v-card-text>

                                        <v-card-text class="pa-0">
                                            <v-alert v-if="staffWarning" type="warning" density="compact"
                                                     variant="tonal" class="mb-2 mt-2">
                                                {{ staffWarning }}
                                            </v-alert>

                                            <p v-if="form.menu_id" class="text-subtitle-1">
                                                合計: {{ totalDuration }}分 /
                                                {{ totalPrice.toLocaleString() }}円
                                            </p>
                                        </v-card-text>
                                    </v-card>
                                </v-col>

                                <!-- Memo -->
                                <v-col cols="12" md="4">
                                    <v-card variant="text">
                                        <v-card-title class="px-0">
                                            予約時メモ
                                        </v-card-title>
                                        <v-card-text class="px-0">
                                            <v-textarea v-model="form.note_from_booker" name="note_from_booker"
                                                        label="予約に関するメモ" rows="3">
                                            </v-textarea>
                                        </v-card-text>
                                    </v-card>
                                </v-col>

                                <!-- Booking Date -->
                                <v-col cols="12" md="8">
                                    <v-card variant="text">
                                        <v-card-title class="px-0">予約日時</v-card-title>

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
                                            <v-row>
                                                <v-col cols="12" sm="6">
                                                    <v-card variant="text">
                                                        <v-card-title class="px-0 text-body-1">
                                                            日付を選択
                                                        </v-card-title>
                                                        <v-card-text class="px-0">
                                                            <v-date-picker v-model="selectedDateValue" hide-header
                                                                           @update:model-value="updateDateFromPicker"
                                                                           @update:year="onPickerYearChange"
                                                                           @update:month="onPickerMonthChange"
                                                                           :allowed-dates="allowedDates"
                                                                           show-adjacent-months>
                                                                <!-- Custom Day Slot for Dots -->
                                                                <template v-slot:day="{ item, props: dayProps }">
                                                                    <v-btn
                                                                           v-bind="dayProps"
                                                                           :style="getDayStyle(item)"
                                                                           class="d-flex justify-center align-center"
                                                                           style="position: relative;"
                                                                           :variant="isToday(item) && !isSelected(item) ? 'outlined' : (isSelected(item) ? 'flat' : 'text')"
                                                                           :color="isSelected(item) ? 'primary' : (allowOffShift && !isWorkingDay(item) ? 'rgba(0, 0, 0, 0.38)' : undefined)"
                                                                           size="small"
                                                                           rounded="circle">
                                                                        {{ getDayNumber(item) }}
                                                                    </v-btn>
                                                                </template>
                                                            </v-date-picker>

                                                            <v-checkbox v-model="allowOffShift" hide-details
                                                                        label="担当スタッフのシフト外も選択可能にする"
                                                                        density="compact" class="mt-2">
                                                            </v-checkbox>

                                                            <!-- 直接入力（シフト外選択可能時のみ表示） -->
                                                            <v-text-field v-if="allowOffShift" v-model="directTimeInput"
                                                                          hide-details
                                                                          label="シフト外の時間を直接入力" readonly
                                                                          append-inner-icon="mdi-clock-edit-outline"
                                                                          class="mt-2"
                                                                          @click="timePickerDialog = true"
                                                                          @click:append-inner="timePickerDialog = true">
                                                            </v-text-field>
                                                        </v-card-text>
                                                    </v-card>
                                                </v-col>

                                                <v-col cols="12" sm="6">
                                                    <v-card variant="text">
                                                        <v-card-title class="pa-0 text-body-1">
                                                            時間を選択
                                                        </v-card-title>

                                                        <v-card-text class="px-0" v-if="!selectedDateValue">
                                                            <p>
                                                                予約日を選択してください
                                                            </p>
                                                        </v-card-text>

                                                        <v-card-text class="px-0"
                                                                     v-else-if="groupedTimeSlots.length > 0">

                                                            <div v-for="group in groupedTimeSlots" :key="group.hour"
                                                                 class="d-flex align-center py-1">
                                                                <div class="text-body-2 font-weight-bold mr-4"
                                                                     style="width: 40px">
                                                                    {{ group.hour }}時
                                                                </div>
                                                                <v-chip-group v-model="selectedTime" column mandatory
                                                                              active-class="primary">
                                                                    <v-chip v-for="time in group.slots" :key="time"
                                                                            :value="time" variant="outlined"
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
                                                    </v-card>
                                                </v-col>
                                            </v-row>
                                        </v-card-text>

                                        <v-card-text class="pa-0">
                                            <v-alert v-if="shiftWarning" type="warning" density="compact"
                                                     variant="tonal" class="mb-2">
                                                {{ shiftWarning }}
                                            </v-alert>

                                            <v-alert v-if="conflictWarning" type="error" density="compact"
                                                     variant="tonal" class="mb-2">
                                                {{ conflictWarning }}
                                            </v-alert>

                                            <p v-if="displayDateTime" class="text-subtitle-1">
                                                予約日時: {{ displayDateTime }}
                                            </p>
                                        </v-card-text>
                                    </v-card>
                                </v-col>

                                <!-- Staff Schedule -->
                                <v-col cols="12" md="4">
                                    <v-card variant="text">
                                        <v-card-title class="px-0">
                                            担当スタッフのシフト・予約
                                        </v-card-title>

                                        <v-card-text class="px-0" v-if="!form.assigned_staff_id">
                                            <p>
                                                担当スタッフを選択してください
                                            </p>
                                        </v-card-text>

                                        <v-card-text class="px-0" v-if="!selectedDateValue">
                                            <p>
                                                予約日を選択してください
                                            </p>
                                        </v-card-text>

                                        <v-card-text class="px-0" v-else>

                                            <v-card variant="text">
                                                <v-card-title class="px-0">
                                                    {{ formattedSelectedDate }} のシフト
                                                </v-card-title>

                                                <v-card-text class="px-0">
                                                    <div class="mb-4">
                                                        <span v-if="dailySchedule && dailySchedule.start">
                                                            {{ dailySchedule.start }} - {{ dailySchedule.end }}
                                                        </span>
                                                        <span v-else class="text-grey">
                                                            登録なし
                                                        </span>
                                                    </div>
                                                </v-card-text>

                                            </v-card>

                                            <v-card variant="text">
                                                <v-card-title class="px-0">
                                                    予約状況:
                                                </v-card-title>

                                                <v-card-text class="px-0" v-if="dailyBookings.length > 0">
                                                    <v-chip v-for="booking in dailyBookings" :key="booking.id"
                                                            class="mb-1 mr-1" color="secondary"
                                                            variant="flat"
                                                            :href="`/shops/${props.shop.slug}/bookings/${booking.id}/edit`"
                                                            target="_blank">
                                                        {{ booking.start }} - {{ booking.end }} {{
                                                            booking.booker_name
                                                        }}
                                                    </v-chip>
                                                </v-card-text>

                                                <v-card-text v-else class="px-0">
                                                    予約はありません
                                                </v-card-text>
                                            </v-card>
                                        </v-card-text>
                                    </v-card>

                                </v-col>

                                <!-- Booker Selection (Fixed) -->
                                <v-col cols="12" md="6">

                                    <v-card variant="text">
                                        <v-card-title class="px-0">
                                            予約者 (編集不可)
                                        </v-card-title>
                                        <v-card-text class="px-0">
                                            <v-text-field v-if="form.booker_number" v-model="form.booker_number"
                                                          label="会員番号" readonly variant="filled">
                                            </v-text-field>

                                            <v-text-field v-model="form.booker_name" name="booker_name" label="予約者名 *"
                                                          readonly variant="filled"
                                                          required placeholder="予約者名を入力">
                                            </v-text-field>
                                            <v-text-field v-model="form.booker_name_kana" name="booker_name_kana"
                                                          label="予約者のよみがな" readonly
                                                          variant="filled">
                                            </v-text-field>

                                            <p class="text-subtitle-1 font-weight-bold mt-4">
                                                連絡先 (編集可能)
                                            </p>
                                            <v-text-field v-model="form.contact_email" name="contact_email"
                                                          label="連絡先メールアドレス *" type="email" required>
                                            </v-text-field>
                                            <v-text-field v-model="form.contact_phone" name="contact_phone"
                                                          label="連絡先電話番号 *" type="tel" required>
                                            </v-text-field>
                                            <v-textarea v-model="form.shop_memo" name="shop_memo"
                                                        label="店舗側のメモ（予約者には表示されません）" rows="3">
                                            </v-textarea>
                                        </v-card-text>
                                    </v-card>
                                </v-col>

                                <!-- Booker History -->
                                <v-col cols="12" md="6" v-if="form.shop_booker_id">
                                    <v-card variant="text">
                                        <v-card-title class="px-0">
                                            予約者履歴
                                        </v-card-title>
                                        <v-card-text class="px-0">
                                            <v-progress-linear v-if="bookerHistoryLoading" indeterminate
                                                               color="primary"></v-progress-linear>
                                            <template v-else-if="bookerHistory">
                                                <div class="d-flex flex-wrap ga-4 mb-4">
                                                    <div>
                                                        <div class="text-caption text-medium-emphasis">予約回数</div>
                                                        <div class="text-h6">{{ bookerHistory.booking_count }}回</div>
                                                    </div>
                                                    <div>
                                                        <div class="text-caption text-medium-emphasis">最終予約日時</div>
                                                        <div class="text-body-1">{{ bookerHistory.last_booking_at || '－'
                                                            }}</div>
                                                    </div>
                                                </div>

                                                <div v-if="bookerHistory.note_from_booker" class="mb-3">
                                                    <div class="text-caption text-medium-emphasis">予約者からのメモ</div>
                                                    <div class="text-body-2 bg-grey-lighten-4 pa-2 rounded">{{
                                                        bookerHistory.note_from_booker }}</div>
                                                </div>

                                                <div v-if="bookerHistory.shop_memo" class="mb-3">
                                                    <div class="text-caption text-medium-emphasis">店舗側メモ</div>
                                                    <div class="text-body-2 bg-amber-lighten-5 pa-2 rounded">{{
                                                        bookerHistory.shop_memo }}</div>
                                                </div>

                                                <div v-if="bookerHistory.recent_bookings.length > 0">
                                                    <div class="text-caption text-medium-emphasis mb-1">直近の予約履歴</div>
                                                    <v-table density="compact">
                                                        <thead>
                                                            <tr>
                                                                <th>日時</th>
                                                                <th>メニュー</th>
                                                                <th>担当</th>
                                                                <th>状態</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="booking in bookerHistory.recent_bookings"
                                                                :key="booking.id">
                                                                <td>{{ booking.start_at }}</td>
                                                                <td>{{ booking.menu_name }}</td>
                                                                <td>{{ booking.staff_name || '－' }}</td>
                                                                <td>
                                                                    <v-chip size="x-small"
                                                                            :color="booking.status === 'cancelled' ? 'error' : booking.status === 'confirmed' ? 'success' : 'warning'">
                                                                        {{ booking.status === 'pending' ? '保留' :
                                                                            booking.status === 'confirmed' ? '確定' : 'キャンセル'
                                                                        }}
                                                                    </v-chip>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </v-table>
                                                </div>
                                                <div v-else class="text-body-2 text-medium-emphasis">
                                                    過去の予約履歴はありません
                                                </div>
                                            </template>
                                        </v-card-text>
                                    </v-card>
                                </v-col>
                            </v-row>

                            <!-- Actions -->
                            <v-card-actions class="px-0 pt-4">
                                <v-btn color="error" @click="deleteDialog = true">
                                    予約を削除する
                                </v-btn>
                                <v-spacer></v-spacer>
                                <v-btn type="submit" color="primary" variant="flat" size="large"
                                       :disabled="!isFormValid" @click="submitForm">
                                    更新する
                                </v-btn>
                            </v-card-actions>
                        </form>
                    </v-col>
                </v-row>

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
                            <v-btn color="blue-darken-1" variant="text" @click="deleteDialog = false">
                                キャンセル
                            </v-btn>
                            <form :action="`/shops/${props.shop.slug}/staff/bookings/${props.booking.id}`" method="POST"
                                  style="display: inline">
                                <input type="hidden" name="_token" :value="props.csrfToken" />
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
        </v-main>
    </v-app>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import ShopHeader from "@/components/common/ShopHeader.vue";
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
interface BookingOption {
    id: number;
    option_id: number;
    option_name: string;
    option_price: number;
    option_duration: number;
}
interface ShopBooker {
    id: number;
    number: string;
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
const form = ref({
    start_at: props.booking.start_at,
    menu_id: Number(props.booking.menu_id),
    option_ids: props.booking.booking_options ? props.booking.booking_options.map(bo => Number(bo.option_id)) : [] as number[],
    assigned_staff_id: Number(props.booking.assigned_staff_id) as number | null,
    shop_booker_id: Number(props.booking.shop_booker_id),
    booker_number: props.booking.booker?.number ?? null,
    booker_name: props.booking.booker_name,
    booker_name_kana: props.booking.booker?.crm?.name_kana ?? "",
    contact_email: props.booking.contact_email,
    contact_phone: props.booking.contact_phone,
    shop_memo: props.booking.shop_memo,
    note_from_booker: props.booking.note_from_booker,
});

const shopTimezone = props.shop.timezone || 'Asia/Tokyo';

// ショップのタイムゾーンを使用して日付と時間のコンポーネントを抽出
const initialDateStr = form.value.start_at
    ? formatInTimeZone(form.value.start_at, shopTimezone, 'yyyy-MM-dd')
    : formatInTimeZone(new Date(), shopTimezone, 'yyyy-MM-dd');

const initialTime = form.value.start_at
    ? formatInTimeZone(form.value.start_at, shopTimezone, 'HH:mm')
    : null;

const selectedTime = ref<string | null>(initialTime);
const directTimeInput = ref<string | null>(initialTime);
const assignedStaffs = ref<Staff[]>([]); // APIから取得したメニューに割り当てられているスタッフを保持

// assignedStaffs を現在のスタッフで初期化し、v-select が選択スロットを即座にレンダリングできるようにする
// これにより、リストが最初は空でも値が存在する場合に "item.raw.profile is undefined" エラーが発生するのを防ぐ
const currentStaff = props.staffs.find(s => s.id === form.value.assigned_staff_id);
if (currentStaff) {
    assignedStaffs.value = [currentStaff];
}

const showAllStaffs = ref(false);
const showAllStaffsModel = computed({
    get: () => showAllStaffs.value,
    set: (value) => {
        if (!value) {
            if (form.value.assigned_staff_id && assignedStaffs.value.length > 0) {
                const inList = assignedStaffs.value.some(s => s.id === form.value.assigned_staff_id);
                if (!inList) {
                    form.value.assigned_staff_id = null;
                }
            } else if (form.value.assigned_staff_id && assignedStaffs.value.length === 0) {
                form.value.assigned_staff_id = null;
            }
        }
        showAllStaffs.value = value;
    }
});
const allowOffShift = ref(false);
const staffWarning = ref<string | null>(null);
const shiftWarning = ref<string | null>(null);
const conflictWarning = ref<string | null>(null);

// --- Booker History State ---
interface BookerHistoryBooking {
    id: number;
    start_at: string;
    menu_name: string;
    staff_name: string | null;
    status: string;
}
interface BookerHistory {
    booking_count: number;
    last_booking_at: string | null;
    note_from_booker: string | null;
    shop_memo: string | null;
    recent_bookings: BookerHistoryBooking[];
}
const bookerHistory = ref<BookerHistory | null>(null);
const bookerHistoryLoading = ref(false);

// --- カレンダーの状態 ---
const workingDays = ref<string[]>([]); // YYYY-MM-DD 文字列
const initialDateObj = new Date(initialDateStr + 'T00:00:00');
const pickerYear = ref(initialDateObj.getFullYear());
const pickerMonth = ref(initialDateObj.getMonth() + 1);

// --- Schedule & Bookings State ---
interface DailyBooking {
    id: number;
    start: string;
    end: string;
    booker_name: string;
}
interface DailySchedule {
    start: string;
    end: string;
}
const dailySchedule = ref<DailySchedule | null>(null);
const dailyBookings = ref<DailyBooking[]>([]);

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

const selectedDateValue = ref<Date | null>(initialDateObj);
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

    try {
        const response = await axios.get(
            `/shops/${props.shop.slug}/staff/api/staffs/${form.value.assigned_staff_id}/timeslots`,
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
    }
};

const fetchDailyScheduleAndBookings = async () => {
    if (!form.value.assigned_staff_id || !formattedSelectedDate.value) {
        dailySchedule.value = null;
        dailyBookings.value = [];
        return;
    }

    try {
        const response = await axios.get(
            `/shops/${props.shop.slug}/staff/api/staffs/${form.value.assigned_staff_id}/schedule`,
            {
                params: { date: formattedSelectedDate.value }
            }
        );
        dailySchedule.value = response.data.schedule;
        dailyBookings.value = response.data.bookings;
    } catch (error) {
        console.error("Failed to fetch schedule/bookings:", error);
        dailySchedule.value = null;
        dailyBookings.value = [];
    }
};

const fetchAssignedStaffs = async (checkAutoEnable = false) => {
    if (!form.value.menu_id) {
        assignedStaffs.value = [];
        return;
    }
    try {
        const url = `/shops/${props.shop.slug}/staff/api/menus/${form.value.menu_id}/staffs`;
        const response = await axios.get(url);

        const newStaffs = response.data.staffs;
        if (checkAutoEnable && form.value.assigned_staff_id && !newStaffs.some((s: Staff) => s.id === form.value.assigned_staff_id)) {
            showAllStaffs.value = true;
        }
        assignedStaffs.value = newStaffs;
    } catch (error) {
        console.error("割り当てスタッフの取得に失敗しました:", error);
        assignedStaffs.value = [];
    }
};

watch(
    () => form.value.assigned_staff_id,
    async () => {
        await checkStaffAssignment();
    }
);





watch(
    [
        () => form.value.menu_id,
        () => form.value.option_ids,
        () => form.value.assigned_staff_id,
        () => formattedSelectedDate.value,
    ],
    async () => {
        await Promise.all([fetchTimeSlots(), fetchDailyScheduleAndBookings()]);
        checkShiftAndConflict();
    }
);

watch([() => form.value.menu_id, showAllStaffs], () => {
    if (form.value.menu_id) {
        fetchAssignedStaffs();
    } else {
        assignedStaffs.value = [];
    }
});

// --- Booker History Fetcher ---
const fetchBookerHistory = async () => {
    if (!form.value.shop_booker_id) {
        bookerHistory.value = null;
        return;
    }
    bookerHistoryLoading.value = true;
    try {
        const response = await axios.get(
            `/shops/${props.shop.slug}/staff/api/bookers/${form.value.shop_booker_id}/history`
        );
        bookerHistory.value = response.data;
    } catch (error) {
        console.error("Failed to fetch booker history:", error);
        bookerHistory.value = null;
    } finally {
        bookerHistoryLoading.value = false;
    }
};

// マウント時に履歴を取得
onMounted(async () => {
    if (form.value.shop_booker_id) {
        fetchBookerHistory();
    }
    if (form.value.menu_id) {
        await fetchAssignedStaffs(true);
        fetchWorkingDays(pickerYear.value, pickerMonth.value);
    }
    fetchTimeSlots();
    fetchDailyScheduleAndBookings();
    checkShiftAndConflict();
    checkStaffAssignment();
});


// --- タイムゾーンと入力の同期ロジック ---

// チップの選択で直接入力を更新
watch(selectedTime, (newVal) => {
    if (newVal) {
        directTimeInput.value = newVal;
    }
});

// 直接入力が一致しない場合、チップの選択をクリア
watch(directTimeInput, (newVal) => {
    if (!newVal) {
        selectedTime.value = null;
        return;
    }
    // 入力がチップグループのスロットと一致するか確認
    const isAvailableSlot = timeSlots.value.includes(newVal);
    if (isAvailableSlot) {
        selectedTime.value = newVal;
    } else {
        selectedTime.value = null;
    }
});

// Update form.start_at
watch(
    [() => formattedSelectedDate.value, directTimeInput],
    ([date, time]) => {
        if (date && time && /^([01]\d|2[0-3]):([0-5]\d)$/.test(time)) {
            form.value.start_at = `${date} ${time}`;
        } else {
            form.value.start_at = "";
        }
        checkShiftAndConflict();
    }
);

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

const displayDateTime = computed(() => {
    if (!formattedSelectedDate.value || !directTimeInput.value) return "";
    return `${formattedSelectedDate.value} ${directTimeInput.value}`;
});

const calculatedEndHint = computed(() => {
    const time = directTimeInput.value;
    if (!time || !/^([01]\d|2[0-3]):([0-5]\d)$/.test(time)) return "HH:MM 形式で入力してください";

    const [hours, minutes] = time.split(":").map(Number);
    const date = new Date();
    date.setHours(hours, minutes + totalDuration.value, 0);
    const endStr = `${String(date.getHours()).padStart(2, "0")}:${String(date.getMinutes()).padStart(2, "0")}`;

    return `終了予定: ${endStr} (${totalDuration.value}分)`;
});

// --- Validation Logic (Client-side) ---
const checkShiftAndConflict = async () => {
    shiftWarning.value = null;
    conflictWarning.value = null;

    if (!form.value.assigned_staff_id || !form.value.start_at) return;

    // 1. Shift Check
    if (!allowOffShift.value) {
        try {
            const response = await axios.get(
                `/shops/${props.shop.slug}/staff/api/bookings/validate-shift`,
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
                shiftWarning.value = "選択された時間は担当スタッフのシフト外です。";
            }
        } catch (error) {
            console.error(error);
        }
    }

    // 2. Conflict Check
    try {
        const response = await axios.get(
            `/shops/${props.shop.slug}/staff/api/bookings/validate-conflict`,
            {
                params: {
                    assigned_staff_id: form.value.assigned_staff_id,
                    start_at: form.value.start_at,
                    menu_id: form.value.menu_id,
                    option_ids: form.value.option_ids,
                    exclude_booking_id: props.booking.id, // 自分自身を除外
                },
            }
        );
        if (!response.data.valid) {
            conflictWarning.value = "担当スタッフの他の予約と重複しています。";
        }
    } catch (error) {
        console.error(error);
    }
};

const checkStaffAssignment = async () => {
    staffWarning.value = null;
    if (!showAllStaffs.value || !form.value.menu_id || !form.value.assigned_staff_id) return;

    try {
        const response = await axios.get(
            `/staff/api/shops/${props.shop.slug}/bookings/validate-staff`,
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
        console.error(error);
    }
};

const isFormValid = computed(() => {
    return (
        !!form.value.menu_id &&
        !!form.value.assigned_staff_id &&
        !!form.value.booker_name &&
        !!form.value.contact_email &&
        !!form.value.contact_phone &&
        !!form.value.start_at &&
        !conflictWarning.value
    );
});

const submitForm = () => {
    const formElement = document.querySelector("form");
    if (formElement) formElement.submit();
};


// --- Dialog State ---
const timePickerDialog = ref(false);
const deleteDialog = ref(false);

// --- Calendar Logic ---
const fetchWorkingDays = async (year: number, month: number) => {
    if (!form.value.assigned_staff_id) {
        workingDays.value = [];
        return;
    }

    const yearMonth = `${year}-${String(month).padStart(2, '0')}`;

    try {
        const response = await axios.get(
            `/shops/${props.shop.slug}/staff/api/staffs/${form.value.assigned_staff_id}/working-days`,
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

const isToday = (dateInput: unknown): boolean => {
    const dateString = getDateString(dateInput);
    if (!dateString) return false;
    const now = new Date();
    const todayString = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    return dateString === todayString;
};

const isSelected = (dateInput: unknown): boolean => {
    const dateString = getDateString(dateInput);
    if (!dateString) return false;
    return dateString === formattedSelectedDate.value;
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

const getDayNumber = (dateInput: unknown): string => {
    const dateString = getDateString(dateInput);
    if (!dateString) return "";
    return String(parseInt(dateString.split('-')[2], 10));
};

const getDayStyle = (date: unknown) => {
    return {};
};

// Picker navigation handlers
const updateDateFromPicker = (newDate: any) => {
    if (newDate instanceof Date) {
        setDate(newDate);
    }
};

const onPickerYearChange = (year: number) => {
    pickerYear.value = year;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};
const onPickerMonthChange = (month: number) => {
    pickerMonth.value = month + 1;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};

// Watchers for calendar data
watch(() => form.value.assigned_staff_id, (newVal) => {
    if (newVal) {
        fetchWorkingDays(pickerYear.value, pickerMonth.value);
    } else {
        workingDays.value = [];
    }
});
</script>

<style scoped>
.container-width-1200 {
    max-width: 1200px;
    margin: 0 auto;
}
</style>
