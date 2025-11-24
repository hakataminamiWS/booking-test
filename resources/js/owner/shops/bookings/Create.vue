<template>
    <v-container>
        <!-- Navigation -->
        <v-row>
            <v-col cols="12">
                <v-btn :href="`/owner/shops/${props.shop.slug}/bookings`" prepend-icon="mdi-arrow-left">
                    予約一覧に戻る
                </v-btn>
            </v-col>
        </v-row>

        <!-- Shop Header -->
        <v-row>
            <v-col cols="12">
                 <p class="text-h5">店舗：{{ props.shop.name }} (店舗ID: {{ props.shop.id }})</p>
            </v-col>
        </v-row>

        <!-- Main Form Card -->
        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title>手動予約登録</v-card-title>
                    <v-card-text>
                        <form :action="`/owner/shops/${props.shop.slug}/bookings`" method="POST">
                            <input type="hidden" name="_token" :value="props.csrfToken" />
                            <input type="hidden" name="start_at" :value="form.start_at" />
                            <input type="hidden" name="shop_booker_id" :value="form.shop_booker_id ?? ''" />

                            <!-- Validation Errors -->
                            <v-alert v-if="props.errors.length > 0" type="error" class="mb-4">
                                <ul>
                                    <li v-for="(error, i) in props.errors" :key="i">{{ error }}</li>
                                </ul>
                            </v-alert>

                            <!-- Menu & Options -->
                            <v-select
                                v-model="form.menu_id"
                                name="menu_id"
                                :items="props.menus"
                                item-title="name"
                                item-value="id"
                                label="メニュー *"
                                required
                                class="mb-4"
                            ></v-select>
                             <v-select
                                v-model="form.option_ids"
                                name="option_ids[]"
                                :items="availableOptions"
                                item-title="name"
                                item-value="id"
                                label="オプション"
                                multiple
                                chips
                                closable-chips
                                :disabled="!form.menu_id"
                                class="mb-4"
                            ></v-select>
                            <v-select
                                v-model="form.assigned_staff_id"
                                name="assigned_staff_id"
                                :items="availableStaffs"
                                item-title="profile.nickname"
                                item-value="id"
                                label="担当スタッフ"
                                :disabled="!form.menu_id"
                                class="mb-4"
                                clearable
                            ></v-select>
                            <p class="text-subtitle-1">合計: {{ totalDuration }}分 / {{ totalPrice.toLocaleString() }}円</p>
                            <v-divider class="my-4"></v-divider>

                            <!-- Date & Time Selection -->
                            <v-row>
                                <v-col cols="12" md="4">
                                    <v-text-field
                                        :model-value="formattedSelectedDate"
                                        label="予約日 *"
                                        readonly
                                        @click="dateDialog = true"
                                        prepend-inner-icon="mdi-calendar"
                                        :disabled="!form.menu_id"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" md="8">
                                    <p class="text-caption mb-2">予約時間 *</p>
                                     <v-chip-group v-model="selectedTime" mandatory active-class="primary">
                                         <v-chip v-for="time in availableTimeSlots" :key="time" :value="time" variant="outlined">
                                             {{ time }}
                                         </v-chip>
                                     </v-chip-group>
                                     <p v-if="!form.menu_id" class="text-caption text-medium-emphasis mt-2">
                                        先にメニューを選択してください
                                     </p>
                                     <p v-else-if="!selectedDateValue" class="text-caption text-medium-emphasis mt-2">
                                        予約日を選択してください
                                     </p>
                                     <p v-else-if="availableTimeSlots.length === 0" class="text-caption text-medium-emphasis mt-2">
                                        予約可能な時間帯がありません。日付またはスタッフを変更してください。
                                     </p>
                                </v-col>
                            </v-row>
                            <v-divider class="my-4"></v-divider>

                            <!-- Booker Selection -->
                            <v-row align="center">
                                <v-col cols="8">
                                    <v-text-field
                                        label="予約者"
                                        :model-value="selectedBookerName"
                                        readonly
                                        placeholder="右のボタンで顧客を選択・登録"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="4">
                                    <v-btn @click="bookerDialog = true" block>選択 / 新規</v-btn>
                                </v-col>
                            </v-row>

                            <!-- Booker Details -->
                            <v-text-field v-model="form.booker_name" name="booker_name" label="予約者名 *" required></v-text-field>
                            <v-text-field v-model="form.contact_email" name="contact_email" label="連絡先メールアドレス" type="email"></v-text-field>
                            <v-text-field v-model="form.contact_phone" name="contact_phone" label="連絡先電話番号" type="tel"></v-text-field>
                            <v-textarea v-model="form.note_from_booker" name="note_from_booker" label="予約者からのメモ" rows="3"></v-textarea>
                            <v-textarea v-model="form.shop_memo" name="shop_memo" label="店舗側のメモ" rows="3"></v-textarea>

                            <!-- Actions -->
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn type="submit" color="primary" :disabled="!form.start_at || !form.booker_name">登録する</v-btn>
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
                            <v-text-field v-model="bookerSearchQuery" label="顧客名、連絡先で検索" prepend-inner-icon="mdi-magnify" variant="solo-filled" flat hide-details class="mb-4"></v-text-field>
                            <v-list lines="two" style="max-height: 400px; overflow-y: auto;">
                                <v-list-item
                                    v-for="booker in filteredBookers"
                                    :key="booker.id"
                                    :title="booker.nickname"
                                    :subtitle="`${booker.contact_email || 'メール未登録'} / ${booker.contact_phone || '電話番号未登録'}`"
                                    :active="selectedBookerInDialog === booker.id"
                                    @click="selectedBookerInDialog = booker.id">
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
                                    <v-col cols="12"><v-text-field v-model="newBookerForm.nickname" label="予約者名 *" required :rules="[v => !!v || '予約者名は必須です']"></v-text-field></v-col>
                                    <v-col cols="12"><v-text-field v-model="newBookerForm.contact_email" label="連絡先メールアドレス" type="email"></v-text-field></v-col>
                                    <v-col cols="12"><v-text-field v-model="newBookerForm.contact_phone" label="連絡先電話番号" type="tel"></v-text-field></v-col>
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
            <v-date-picker v-model="selectedDateValue" @update:model-value="dateDialog = false" show-adjacent-months></v-date-picker>
        </v-dialog>
    </v-container>
</template>

<script setup lang="ts">

import { ref, computed, onMounted, watch } from 'vue';

import { useBookingAvailability } from '@/composables/useBookingAvailability';



// --- Type Definitions ---

interface BusinessHour { weekday: number; start_time: string; end_time: string; is_closed: boolean; }

interface SpecialDay { date: string; start_time: string; end_time: string; }

interface Shop { id: number; name: string; slug: string; businessHoursRegular: BusinessHour[], specialOpenDays: SpecialDay[], specialClosedDays: SpecialDay[] }

interface Menu { id: number; name: string; price: number; duration: number; options: Option[]; staffs: Staff[]; }

interface Option { id: number; name: string; price: number; additional_duration: number; }

interface StaffSchedule { weekday: number; start_time: string; end_time: string; }

interface Staff { id: number; profile: { nickname: string; }; schedules: StaffSchedule[]; }

interface Booker { id: number; nickname: string; contact_email: string; contact_phone: string; }

interface Booking { id: number; start_at: string; end_at: string; assigned_staff_id: number; }

interface Props { shop: Shop; menus: Menu[]; staffs: Staff[]; bookers: Booker[]; bookings: Booking[]; errors: string[]; oldInput: { [key: string]: any } | null; csrfToken: string; }



// --- Props ---

const props = defineProps<Props>();



// --- Form State ---

const form = ref({

    start_at: '',

    menu_id: null as number | null,

    option_ids: [] as number[],

    assigned_staff_id: null as number | null,

    shop_booker_id: null as number | null,

    booker_name: '',

    contact_email: '',

    contact_phone: '',

    note_from_booker: '',

    shop_memo: '',

});

const selectedTime = ref<string | null>(null);



// --- Computed Properties for Calculation ---

const totalPrice = computed(() => {

    let total = selectedMenu.value?.price ?? 0;

    const selectedOptions = availableOptions.value.filter(opt => form.value.option_ids.includes(opt.id));

    selectedOptions.forEach(opt => { total += opt.price; });

    return total;

});



const totalDuration = computed(() => {

    let total = selectedMenu.value?.duration ?? 0;

    const selectedOptions = availableOptions.value.filter(opt => form.value.option_ids.includes(opt.id));

    selectedOptions.forEach(opt => { total += opt.additional_duration; });

    return total;

});



// --- Composables ---

const {

    selectedDate: selectedDateValue,

    setDate,

    availableTimeSlots

} = useBookingAvailability(

    computed(() => props.shop),

    computed(() => props.bookings),

    computed(() => props.staffs),

    totalDuration,

    computed(() => form.value.assigned_staff_id)

);



// --- Dialog State ---

const bookerDialog = ref(false);

const dialogTab = ref('select');

const bookerSearchQuery = ref('');

const selectedBookerInDialog = ref<number | null>(null);

const newBookerForm = ref({ nickname: '', contact_email: '', contact_phone: '' });

const dateDialog = ref(false);



// --- Computed Properties for UI ---

const selectedMenu = computed((): Menu | undefined => props.menus.find(m => m.id === form.value.menu_id));

const availableOptions = computed((): Option[] => selectedMenu.value?.options ?? []);

const availableStaffs = computed((): Staff[] => {

    // TODO: Enhance this to filter by shift based on selectedDate

    return selectedMenu.value?.staffs ?? [];

});



const selectedBookerName = computed((): string => {

    if (!form.value.shop_booker_id && form.value.booker_name) return form.value.booker_name;

    if (form.value.shop_booker_id) {

        const booker = props.bookers.find(b => b.id === form.value.shop_booker_id);

        return booker?.nickname ?? '選択されていません';

    }

    return '選択されていません';

});



const filteredBookers = computed((): Booker[] => {

    if (!bookerSearchQuery.value) return props.bookers;

    const query = bookerSearchQuery.value.toLowerCase();

    return props.bookers.filter(booker =>

        booker.nickname.toLowerCase().includes(query) ||

        (booker.contact_email && booker.contact_email.toLowerCase().includes(query)) ||

        (booker.contact_phone && booker.contact_phone.includes(query))

    );

});



const formattedSelectedDate = computed(() => {

    if (!selectedDateValue.value) return '';

    const d = new Date(selectedDateValue.value);

    const year = d.getFullYear();

    const month = (`0` + (d.getMonth() + 1)).slice(-2);

    const day = (`0` + d.getDate()).slice(-2);

    return `${year}-${month}-${day}`;

});



// --- Watchers ---

watch(() => form.value.menu_id, () => {

    form.value.option_ids = [];

    form.value.assigned_staff_id = null;

    selectedTime.value = null; // Reset time when menu changes

});



watch(() => form.value.shop_booker_id, (newBookerId) => {

    if (newBookerId) {

        const booker = props.bookers.find(b => b.id === newBookerId);

        if (booker) {

            form.value.booker_name = booker.nickname;

            form.value.contact_email = booker.contact_email;

            form.value.contact_phone = booker.contact_phone;

        }

    } else {

        if (dialogTab.value !== 'create') {

             form.value.booker_name = '';

             form.value.contact_email = '';

             form.value.contact_phone = '';

        }

    }

});



watch([selectedDateValue, selectedTime], ([newDate, newTime]) => {

    if (newDate && newTime) {

        form.value.start_at = `${formattedSelectedDate.value} ${newTime}:00`;

    } else {

        form.value.start_at = '';

    }

});



// --- Dialog Methods ---

function confirmBookerSelection() {

    if (dialogTab.value === 'select') {

        if (selectedBookerInDialog.value) {

            form.value.shop_booker_id = selectedBookerInDialog.value;

        }

    } else if (dialogTab.value === 'create') {

        if (newBookerForm.value.nickname) {

            form.value.shop_booker_id = null;

            form.value.booker_name = newBookerForm.value.nickname;

            form.value.contact_email = newBookerForm.value.contact_email;

            form.value.contact_phone = newBookerForm.value.contact_phone;

        } else {

            alert('予約者名は必須です。');

            return;

        }

    }

    bookerDialog.value = false;

}



function cancelBookerSelection() {

    selectedBookerInDialog.value = form.value.shop_booker_id;

    newBookerForm.value = { nickname: '', contact_email: '', contact_phone: '' };

    bookerDialog.value = false;

}



watch(bookerDialog, (isOpen) => {

    if (isOpen) {

        selectedBookerInDialog.value = form.value.shop_booker_id;

        newBookerForm.value = { nickname: '', contact_email: '', contact_phone: '' };

        dialogTab.value = form.value.shop_booker_id ? 'select' : 'create';

    }

})



// --- Lifecycle Hooks ---

onMounted(() => {

    setDate(new Date()); // Set today as initial date



    if (props.oldInput) {

        form.value.menu_id = props.oldInput.menu_id ? Number(props.oldInput.menu_id) : null;

        form.value.option_ids = (props.oldInput.option_ids ?? []).map(Number);

        form.value.assigned_staff_id = props.oldInput.assigned_staff_id ? Number(props.oldInput.assigned_staff_id) : null;

        form.value.shop_booker_id = props.oldInput.shop_booker_id ? Number(props.oldInput.shop_booker_id) : null;

        form.value.booker_name = props.oldInput.booker_name ?? '';

        form.value.contact_email = props.oldInput.contact_email ?? '';

        form.value.contact_phone = props.oldInput.contact_phone ?? '';

        form.value.note_from_booker = props.oldInput.note_from_booker ?? '';

        form.value.shop_memo = props.oldInput.shop_memo ?? '';



        if(props.oldInput.start_at) {

            const d = new Date(props.oldInput.start_at);

            setDate(d);

            selectedTime.value = (`0` + d.getHours()).slice(-2) + ':' + (`0` + d.getMinutes()).slice(-2);

        }

    }

});

</script>
