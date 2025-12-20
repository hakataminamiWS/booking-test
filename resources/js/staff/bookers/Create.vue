<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn :href="bookersIndexUrl" prepend-icon="mdi-arrow-left">
                    予約者一覧に戻る
                </v-btn>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="shop" />
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title>予約者新規登録</v-card-title>
                    <v-card-text>
                        <v-alert
                                 v-if="props.errors.length > 0"
                                 type="error"
                                 class="mb-4"
                                 closable>
                            <ul>
                                <li
                                    v-for="(error, index) in props.errors"
                                    :key="index">
                                    {{ error }}
                                </li>
                            </ul>
                        </v-alert>

                        <form :action="formAction" method="POST">
                            <input
                                   type="hidden"
                                   name="_token"
                                   :value="csrfToken" />

                            <v-card variant="outlined" class="mb-4">
                                <v-card-title class="text-h6">
                                    予約者・店舗共通情報
                                </v-card-title>
                                <v-card-text>
                                    <v-text-field
                                                  v-model="formData.name"
                                                  name="name"
                                                  label="名前"
                                                  required
                                                  :rules="[rules.required, rules.maxLength(255)]"
                                                  hint="お客様の呼び名などを入力します。"
                                                  persistent-hint
                                                  class="mb-4"></v-text-field>

                                    <v-text-field
                                                  v-model="formData.contact_email"
                                                  name="contact_email"
                                                  label="連絡先メールアドレス"
                                                  type="email"
                                                  required
                                                  :rules="[rules.required, rules.email]"
                                                  hint="予約通知などに使用するメールアドレス。"
                                                  persistent-hint
                                                  class="mb-4"></v-text-field>

                                    <v-text-field
                                                  v-model="formData.contact_phone"
                                                  name="contact_phone"
                                                  label="連絡先電話番号"
                                                  type="tel"
                                                  required
                                                  :rules="[rules.required, rules.maxLength(20)]"
                                                  hint="お客様の電話番号。"
                                                  persistent-hint
                                                  class="mb-4"></v-text-field>

                                    <v-textarea
                                                v-model="formData.note_from_booker"
                                                name="note_from_booker"
                                                label="予約者からのメモ"
                                                hint="予約者が入力するメモ。（任意）"
                                                persistent-hint
                                                class="mb-4"></v-textarea>
                                </v-card-text>
                            </v-card>

                            <v-card variant="outlined">
                                <v-card-title class="text-h6">
                                    店舗管理用の情報
                                </v-card-title>
                                <v-card-text>
                                    <v-text-field
                                                  v-model="formData.name_kana"
                                                  name="name_kana"
                                                  label="よみかた"
                                                  :rules="[rules.maxLength(255)]"
                                                  hint="（任意）"
                                                  persistent-hint
                                                  class="mb-4"></v-text-field>

                                    <v-textarea
                                                v-model="formData.shop_memo"
                                                name="shop_memo"
                                                label="店舗側メモ"
                                                hint="お客様に関する店舗側で管理するメモ。（任意）"
                                                persistent-hint
                                                class="mb-4"></v-textarea>

                                    <v-text-field
                                                  v-model="formData.last_booking_at"
                                                  name="last_booking_at"
                                                  label="最終予約日"
                                                  :rules="lastBookingAtRule"
                                                  hint="YYYY-MM-DD形式で入力。（任意）"
                                                  persistent-hint
                                                  class="mb-4"
                                                  append-inner-icon="mdi-calendar"
                                                  @click:append-inner="dateDialog = true"></v-text-field>

                                    <v-dialog v-model="dateDialog" max-width="400px">
                                        <v-card>
                                            <v-date-picker
                                                           v-model="pickerDate"
                                                           @update:model-value="setDate"
                                                           show-adjacent-months></v-date-picker>
                                        </v-card>
                                    </v-dialog>

                                    <v-text-field
                                                  v-model="formData.booking_count"
                                                  @update:model-value="formData.booking_count = formatNumericInput($event)"
                                                  name="booking_count"
                                                  label="予約回数"
                                                  inputmode="numeric"
                                                  :rules="[rules.numeric]"
                                                  hint="数値を入力。（任意）"
                                                  persistent-hint
                                                  class="mb-4"></v-text-field>
                                </v-card-text>
                            </v-card>

                            <v-card-actions class="mt-4">
                                <v-spacer></v-spacer>
                                <v-btn
                                       type="submit"
                                       color="primary"
                                       :disabled="!isFormValid"
                                       @click="validateForm">登録する</v-btn>
                            </v-card-actions>
                        </form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import ShopHeader from "@/components/common/ShopHeader.vue";
import { formatNumericInput } from "@/composables/useNumericInput";

const props = defineProps({
    shop: { type: Object, required: true },
    csrfToken: { type: String, required: true },
    errors: { type: Array, default: () => [] },
    oldInput: { type: Object, default: () => ({}) },
});

const bookersIndexUrl = computed(
    () => `/shops/${props.shop.slug}/staff/bookers`
);
const formAction = computed(() => `/shops/${props.shop.slug}/staff/bookers`);

const formData = ref({
    name: "",
    name_kana: "",
    contact_email: "",
    contact_phone: "",
    note_from_booker: "",
    shop_memo: "",
    last_booking_at: "",
    booking_count: "",
});

onMounted(() => {
    if (props.oldInput && Object.keys(props.oldInput).length > 0) {
        formData.value.name = props.oldInput.name ?? "";
        formData.value.name_kana = props.oldInput.name_kana ?? "";
        formData.value.contact_email = props.oldInput.contact_email ?? "";
        formData.value.contact_phone = props.oldInput.contact_phone ?? "";
        formData.value.note_from_booker = props.oldInput.note_from_booker ?? "";
        formData.value.shop_memo = props.oldInput.shop_memo ?? "";
        formData.value.last_booking_at = props.oldInput.last_booking_at ?? "";
        formData.value.booking_count = props.oldInput.booking_count ?? "";
    }
});

const rules = {
    required: (value: any) => !!value || "必須項目です。",
    email: (value: string) => /.+@.+\..+/.test(value) || "有効なメールアドレスを入力してください。",
    maxLength: (length: number) => (value: string) => !value || value.length <= length || `${length}文字以内で入力してください。`,
    numeric: (value: string) => /^[0-9]*$/.test(value) || "半角数字で入力してください。",
};

const lastBookingAtRule = [
    (v: string) => {
        if (!v) return true; // 任意なので空はOK
        return /^\d{4}-\d{2}-\d{2}$/.test(v) || "YYYY-MM-DD 形式で入力してください。";
    }
];

const isFormValid = computed(() => {
    // Required fields check
    const requiredValid = !!formData.value.name &&
        !!formData.value.contact_email &&
        !!formData.value.contact_phone;
    if (!requiredValid) return false;

    // Rules check
    const nameValid = rules.maxLength(255)(formData.value.name) === true;
    const emailValid = rules.email(formData.value.contact_email) === true;
    const phoneValid = rules.maxLength(20)(formData.value.contact_phone) === true;
    const kanaValid = rules.maxLength(255)(formData.value.name_kana) === true;
    const countValid = rules.numeric(String(formData.value.booking_count)) === true; // Convert to string for safety
    const dateValid = lastBookingAtRule[0](formData.value.last_booking_at) === true;

    return nameValid && emailValid && phoneValid && kanaValid && countValid && dateValid;
});

const validateForm = (event: Event) => {
    if (!isFormValid.value) {
        event.preventDefault();
        alert("必須項目を正しく入力してください。");
        return;
    }
};

// --- Date Picker for last_booking_at ---
const dateDialog = ref(false);
const pickerDate = ref<Date | null>(new Date());

const setDate = () => {
    if (pickerDate.value) {
        const d = pickerDate.value;
        const year = d.getFullYear();
        const month = (d.getMonth() + 1).toString().padStart(2, '0');
        const day = d.getDate().toString().padStart(2, '0');
        formData.value.last_booking_at = `${year}-${month}-${day}`;
    }
    dateDialog.value = false;
};
</script>
