<template>
    <StaffLayout :shop="shop" currentPage="bookings">
        <v-main>
            <v-container fluid class="container-width-1200">
                <!-- ナビゲーション (Removed as per design) -->

                <!-- ショップヘッダー (Removed as StaffLayout handles it) -->

                <!-- メインフォームカード -->
                <v-row>
                    <v-col cols="12">
                        <form
                              id="booking-create-form"
                              :action="`/shops/${props.shop.slug}/staff/bookings`"
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

                            <!-- バリデーションエラー -->
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
                                <!-- メニュー・オプション・スタッフ -->
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

                                <!-- メモ -->
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

                                <!-- 予約日時 -->
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
                                                                           @update:year="onPickerYearChange"
                                                                           @update:month="onPickerMonthChange"
                                                                           :allowed-dates="allowedDates"
                                                                           show-adjacent-months>
                                                                <!-- カレンダーの日付スロット -->
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

                                            <v-alert v-if="conflictWarning" type="warning" density="compact"
                                                     variant="tonal" class="mb-2">
                                                {{ conflictWarning }}
                                            </v-alert>

                                            <p v-if="displayDateTime" class="text-subtitle-1">
                                                予約日時: {{ displayDateTime }}
                                            </p>
                                        </v-card-text>
                                    </v-card>
                                </v-col>

                                <!-- スタッフスケジュール -->
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
                                                            :href="`/shops/${props.shop.slug}/staff/bookings/${booking.id}/edit`"
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

                                <!-- 予約者選択 -->
                                <v-col cols="12" md="6">

                                    <v-card variant="text">
                                        <v-card-title class="px-0">
                                            予約者
                                        </v-card-title>
                                        <v-card-text class="px-0">
                                            <v-text-field v-model="form.booker_name" name="booker_name" label="予約者名 *"
                                                          :readonly="!!form.shop_booker_id"
                                                          required placeholder="予約者名を入力">
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
                                                          :readonly="!!form.shop_booker_id">
                                            </v-text-field>
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

                                <!-- 予約者履歴（既存予約者選択時のみ表示） -->
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
                        </form>
                    </v-col>
                </v-row>

                <!-- 予約者選択ダイアログ -->
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
                                        <v-list-item v-for="booker in filteredBookers" :key="booker.id"
                                                     :title="booker.name" :subtitle="`${booker.contact_email || 'メール未登録'
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
                                            <v-col cols="12"><v-text-field v-model="newBookerForm.nickname"
                                                              label="予約者名 *" required
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
                                            <v-col cols="12"><v-textarea v-model="newBookerForm.shop_memo"
                                                            label="店舗側のメモ"
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

                <!-- 時間選択ダイアログ -->
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
        </v-main>

        <BookingStickyFooter :menu-name="selectedMenu?.name" :staff-name="selectedStaffName"
                             :date-time="displayDateTime"
                             :total-price="totalPrice" submit-label="登録する" :disabled="!isFormValid"
                             @submit="submitForm" />
    </StaffLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import StaffLayout from "@/components/staff/StaffLayout.vue";
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
    bookers: ShopBooker[];
    bookings: Booking[];
    errors: string[];
    oldInput: { [key: string]: any } | null;
    csrfToken: string;
}

// --- プロパティ ---
const props = defineProps<Props>();

// --- フォーム状態 ---
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

// --- タイムゾーン ---
const shopTimezone = computed(() => props.shop.timezone || 'Asia/Tokyo');

const selectedTime = ref<string | null>(null);
const directTimeInput = ref<string | null>(null);
const assignedStaffs = ref<Staff[]>([]); // APIから取得したメニューに割り当てられているスタッフを保持
const showAllStaffs = ref(false);
const allowOffShift = ref(false);
const staffWarning = ref<string | null>(null);
const shiftWarning = ref<string | null>(null);
const conflictWarning = ref<string | null>(null);

// --- 予約者履歴状態 ---
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

// --- カレンダー状態 ---
const workingDays = ref<string[]>([]); // YYYY-MM-DD strings
const pickerYear = ref(new Date().getFullYear());
const pickerMonth = ref(new Date().getMonth() + 1);

// --- スケジュール＆予約状態 ---
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

// --- 時間枠の状態とロジック ---
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
        console.error("スケジュール/予約の取得に失敗しました:", error);
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

// --- 計算用算出プロパティ ---
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

// --- フッター表示ロジック ---
const selectedStaffName = computed(() => {
    if (!form.value.assigned_staff_id) return undefined;
    const staff = availableStaffs.value.find(s => s.id === form.value.assigned_staff_id);
    return staff?.profile.nickname;
});

const displayDateTime = computed(() => {
    const effectiveTime = selectedTime.value || directTimeInput.value;
    if (!formattedSelectedDate.value || !effectiveTime || !selectedDateValue.value) return undefined;

    // 曜日を取得
    const dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][selectedDateValue.value.getDay()];

    // 終了時間を計算
    const [hours, minutes] = effectiveTime.split(":").map(Number);
    const date = new Date();
    date.setHours(hours, minutes + totalDuration.value, 0);
    const endStr = `${String(date.getHours()).padStart(2, "0")}:${String(date.getMinutes()).padStart(2, "0")}`;
    return `${formattedSelectedDate.value}(${dayOfWeek}) ${effectiveTime}~${endStr} (${totalDuration.value}分)`;
});

const isFormValid = computed(() => {
    return (
        !!form.value.menu_id &&
        !!form.value.assigned_staff_id &&
        !!form.value.booker_name &&
        !!form.value.contact_email &&
        !!form.value.contact_phone &&
        !!form.value.start_at
    );
});

const submitForm = () => {
    const formElement = document.getElementById("booking-create-form") as HTMLFormElement;
    if (formElement) formElement.submit();
};

// --- ダイアログ状態 ---
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

const timePickerDialog = ref(false);

// --- カレンダーロジック ---
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
        console.error("シフトデータの取得に失敗しました:", error);
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
    if (!dateString || !formattedSelectedDate.value) return false;
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

// Helper: 日付の数字を取得
const getDayNumber = (dateInput: unknown): string => {
    const dateString = getDateString(dateInput);
    if (!dateString) return "";
    return String(parseInt(dateString.split('-')[2], 10));
};

const getDayStyle = (date: unknown) => {
    return {};
};

// ピッカーナビゲーションハンドラ
const onPickerYearChange = (year: number) => {
    pickerYear.value = year;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};
const onPickerMonthChange = (month: number) => {
    pickerMonth.value = month + 1;
    fetchWorkingDays(pickerYear.value, pickerMonth.value);
};

const filteredBookers = computed((): ShopBooker[] => {
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

// ========================================
// --- Watchers ---
// ========================================

// 1. 予約可能な時間枠の取得
watch(
    [
        () => form.value.menu_id,
        () => form.value.option_ids,
        () => form.value.assigned_staff_id,
        () => formattedSelectedDate.value,
    ],
    fetchTimeSlots
);

// 2. 担当スタッフのシフト・予約状況の取得
watch(
    [
        () => form.value.assigned_staff_id,
        () => formattedSelectedDate.value,
    ],
    fetchDailyScheduleAndBookings
);

// 3. メニュー変更時の処理（オプション・時間リセット + 担当スタッフの制御）
watch(
    () => form.value.menu_id,
    () => {
        // オプション・日時のリセット
        form.value.option_ids = [];
        selectedTime.value = null;
        directTimeInput.value = null;
        selectedDateValue.value = null;

        // 担当スタッフの制御
        // 選択済みの担当スタッフをクリア
        form.value.assigned_staff_id = null;

        // スタッフ割り当てが必須の場合はAPIから取得
        if (selectedMenu.value) {
            if (selectedMenu.value.requires_staff_assignment) {
                fetchAssignedStaffs();
            }
        } else {
            // メニューが未選択の場合はスタッフリストをクリア
            assignedStaffs.value = [];
        }

    }
);

// 4. 担当スタッフ変更時の処理（勤務日取得 + 割り当てバリデーション）
watch(
    () => form.value.assigned_staff_id,
    async (newStaffId) => {
        // 勤務日の取得（カレンダー用）
        if (newStaffId) {
            fetchWorkingDays(pickerYear.value, pickerMonth.value);
        } else {
            workingDays.value = [];
        }

        // スタッフ割り当てバリデーション
        await checkStaffAssignment();
    }
);

// 5. 「メニューに割り当たっていない担当スタッフも表示する」チェックボックスがOFF時の処理
watch(
    showAllStaffs,
    (newVal) => {
        // OFFになった場合、選択中のスタッフがリストにない場合は初期化
        if (!newVal && form.value.assigned_staff_id) {
            const isStaffInList = availableStaffs.value.some(
                (s) => s.id === form.value.assigned_staff_id
            );
            if (!isStaffInList) {
                form.value.assigned_staff_id = null;
            }
        }
        // チェックが変わったらバリデーションも再実行
        checkStaffAssignment();
    }
);

// 6. 予約者選択時のフォーム反映 + 履歴取得
const fetchBookerHistory = async () => {
    const bookerId = form.value.shop_booker_id;
    if (!bookerId) {
        bookerHistory.value = null;
        if (dialogTab.value !== "create") {
            form.value.booker_name = "";
            form.value.booker_name_kana = "";
            form.value.contact_email = "";
            form.value.contact_phone = "";
            form.value.shop_memo = "";
        }
        return;
    }

    const booker = props.bookers.find((b) => b.id === bookerId);
    if (booker) {
        form.value.booker_name = booker.name;
        form.value.booker_name_kana = booker.crm?.name_kana ?? "";
        form.value.contact_email = booker.contact_email;
        form.value.contact_phone = booker.contact_phone;
        form.value.shop_memo = booker.crm?.shop_memo ?? "";
    }
    // 予約者履歴を取得
    bookerHistoryLoading.value = true;
    try {
        const response = await axios.get(
            `/shops/${props.shop.slug}/staff/api/bookers/${bookerId}/history`
        );
        bookerHistory.value = response.data;
    } catch (error) {
        bookerHistory.value = null;
    } finally {
        bookerHistoryLoading.value = false;
    }
};
watch(
    () => form.value.shop_booker_id,
    fetchBookerHistory
);
// 7. selectedTime (Chip) が変化したときの処理
watch(
    selectedTime,
    (newVal) => {
        // Chip が選択されたら、直接入力をクリア（無限ループ防止: 値がある場合のみ）
        if (newVal && directTimeInput.value) {
            directTimeInput.value = null;
        }
    }
);

// 8. directTimeInput が変化したときの処理
watch(
    directTimeInput,
    (newVal) => {
        // 直接入力が設定されたら、Chip の選択を解除（無限ループ防止: 値がある場合のみ）
        if (newVal && selectedTime.value) {
            selectedTime.value = null;
        }
    }
);

// 9. 予約日時（form.start_at）の構築とバリデーション
watch(
    [() => formattedSelectedDate.value, selectedTime, directTimeInput],
    async ([newDate, chipTime, inputTime]) => {
        // 有効な時間を取得（Chip または 直接入力）
        const effectiveTime = chipTime || inputTime;

        // start_at の構築 & 警告チェック
        if (newDate && effectiveTime && /^([01]\d|2[0-3]):([0-5]\d)$/.test(effectiveTime)) {
            const newStartAt = `${newDate} ${effectiveTime}:00`;
            if (form.value.start_at !== newStartAt) {
                form.value.start_at = newStartAt;
            }
        } else {
            form.value.start_at = "";
        }
        await checkShiftAndConflict();
    }
);

// 10. 予約者ダイアログの初期化
watch(
    bookerDialog,
    (isOpen) => {
        if (isOpen) {
            selectedBookerInDialog.value = form.value.shop_booker_id;
            newBookerForm.value = {
                nickname: "",
                booker_name_kana: "",
                contact_email: "",
                contact_phone: "",
                shop_memo: "",
            };
            // 常に 'select' タブをデフォルトにする
            dialogTab.value = "select";
        }
    }
);



// --- バリデーション関数 ---
const checkShiftAndConflict = async () => {
    shiftWarning.value = null;
    conflictWarning.value = null;

    if (!form.value.assigned_staff_id || !form.value.start_at) return;

    // 1. シフトチェック（直接入力時のみ実行 - TimeChip選択はシフト内のみなので不要）
    if (directTimeInput.value) {
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
                shiftWarning.value = "※この日時は担当スタッフのシフト外です";
            }
        } catch (error) {
            console.error("シフトのバリデーションに失敗しました:", error);
        }
    }

    // 2. 競合チェック
    try {
        const response = await axios.get(
            `/shops/${props.shop.slug}/staff/api/bookings/validate-conflict`,
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
            conflictWarning.value = "※この時間帯には既に別の予約が入っています";
        }
    } catch (error) {
        console.error("競合のバリデーションに失敗しました:", error);
    }
};

const checkStaffAssignment = async () => {
    staffWarning.value = null;
    if (!showAllStaffs.value || !form.value.menu_id || !form.value.assigned_staff_id) return;
    if (selectedMenu.value && !selectedMenu.value.requires_staff_assignment) return;

    try {
        const response = await axios.get(
            `/shops/${props.shop.slug}/staff/api/bookings/validate-staff`,
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


// --- ダイアログメソッド ---
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

// --- ライフサイクルフック ---
onMounted(async () => {
    // 予約者の履歴を取得
    // 新規登録時は不要

    // フォームエラー(oldInput) の適用
    // 配列の場合は中身があるかチェック（LaravelのgetOldInputは空配列を返すため）
    const hasOldInput = props.oldInput && Object.keys(props.oldInput).length > 0;
    if (hasOldInput) {
        const old = props.oldInput as { [key: string]: any }; // 型アサーション
        form.value.menu_id = old.menu_id
            ? Number(old.menu_id)
            : null;
        form.value.option_ids = (old.option_ids ?? []).map(Number);
        form.value.assigned_staff_id = old.assigned_staff_id
            ? Number(old.assigned_staff_id)
            : null;
        form.value.shop_booker_id = old.shop_booker_id
            ? Number(old.shop_booker_id)
            : null;
        form.value.booker_name = old.booker_name ?? "";
        form.value.booker_name_kana = old.booker_name_kana ?? "";
        form.value.contact_email = old.contact_email ?? "";
        form.value.contact_phone = old.contact_phone ?? "";
        form.value.shop_memo = old.shop_memo ?? "";
        form.value.note_from_booker = old.note_from_booker ?? "";

        if (old.start_at) {
            const d = new Date(old.start_at);
            setDate(d);
            // 時間の復元
            const time =
                (`0` + d.getHours()).slice(-2) +
                ":" +
                (`0` + d.getMinutes()).slice(-2);
            selectedTime.value = time;
            directTimeInput.value = time;
        }
    }

    // データフェッチ
    if (form.value.menu_id) {
        // oldInputがある場合、スタッフ割り当てバリデーションのためにリストを取得
        // この時、以前選んでいたスタッフがリストになくても、IDがセットされていれば form.value.assigned_staff_id に値が入っている
        // checkAutoEnable = true で呼ぶことで、リストになければ「全スタッフ表示」をONにする
        await fetchAssignedStaffs(true);
    }

    if (form.value.assigned_staff_id) {
        fetchWorkingDays(pickerYear.value, pickerMonth.value);
    }

    // バリデーション実行
    await Promise.all([
        fetchTimeSlots(),
        fetchDailyScheduleAndBookings(),
    ]);

    checkShiftAndConflict();
    checkStaffAssignment();
});
</script>

<style scoped>
.container-width-1200 {
    max-width: 1200px;
}
</style>
