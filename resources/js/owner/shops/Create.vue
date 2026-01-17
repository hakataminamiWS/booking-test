<template>
    <OwnerSimpleLayout>
        <v-container>
            <v-row>
                <v-col cols="12">
                    <v-btn href="/owner/shops" prepend-icon="mdi-arrow-left">
                        店舗一覧に戻る
                    </v-btn>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="12">
                    <v-card>
                        <v-card-title>店舗の新規登録</v-card-title>
                        <v-divider></v-divider>
                        <v-card-text>
                            <form action="/owner/shops" method="POST">
                                <input
                                       type="hidden"
                                       name="_token"
                                       :value="props.csrfToken" />

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

                                <v-text-field
                                              v-model="form.name"
                                              name="name"
                                              label="店舗名 *"
                                              required
                                              :rules="[rules.required]"></v-text-field>

                                <v-text-field
                                              v-model="form.slug"
                                              name="slug"
                                              label="店舗 ID *"
                                              :rules="[rules.required, rules.slugFormat, rules.slugReserved]"
                                              :error-messages="slugErrorMessages"
                                              :success-messages="slugSuccessMessages"
                                              @blur="checkSlugUniqueness"
                                              required
                                              hint="予約ページのURLに使われる半角英数字とハイフンのみの文字列です。例: shop-1"
                                              persistent-hint></v-text-field>

                                <v-text-field
                                              v-model="form.email"
                                              name="email"
                                              label="店舗メールアドレス *"
                                              type="email"
                                              required
                                              :rules="[rules.required]"
                                              hint="予約完了／キャンセル時に、予約システムから送信される確認メールの受信先を指定してください。"
                                              persistent-hint></v-text-field>
                                <v-select
                                          v-model="form.time_slot_interval"
                                          name="time_slot_interval"
                                          :items="[15, 30, 60]"
                                          label="予約枠の間隔（分） *"
                                          required
                                          :rules="[rules.required]"></v-select>

                                <v-radio-group v-model="form.accepts_online_bookings" name="accepts_online_bookings"
                                               required :rules="[rules.required]">
                                    <template v-slot:label>
                                        <div>オンライン予約受付 *</div>
                                    </template>
                                    <v-radio label="受け付ける" :value="1"></v-radio>
                                    <v-radio label="受け付けない" :value="0"></v-radio>
                                </v-radio-group>

                                <v-text-field v-model="form.timezone" name="timezone" label="タイムゾーン"
                                              readonly></v-text-field>

                                <v-text-field v-model.number="form.cancellation_deadline_minutes
                                    " @update:model-value="
                                        form.cancellation_deadline_minutes =
                                        formatNumericInput($event) as any
                                        " name="cancellation_deadline_minutes" label="キャンセル期限（分前） *"
                                              inputmode="numeric" required :rules="[rules.required, rules.numeric]"
                                              hint="予約の何分前までお客様によるキャンセルを許可するか設定します。(例: 1440 分 = 24 時間前)"
                                              persistent-hint></v-text-field>

                                <v-text-field v-model.number="form.booking_deadline_minutes
                                    " @update:model-value="
                                        form.booking_deadline_minutes =
                                        formatNumericInput($event) as any
                                        " name="booking_deadline_minutes" label="予約締切（分前） *" inputmode="numeric"
                                              required :rules="[rules.required, rules.numeric]"
                                              hint="予約の何分前でオンライン予約の受付を締め切るか設定します。(0 は直前まで許可)"
                                              persistent-hint></v-text-field>

                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn type="submit" color="primary" :disabled="!isFormValid">登録する</v-btn>
                                </v-card-actions>
                            </form>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </OwnerSimpleLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import OwnerSimpleLayout from "@/components/owner/OwnerSimpleLayout.vue";
import { formatNumericInput } from "@/composables/useNumericInput";

const props = defineProps<{
    errors: string[];
    oldInput: { [key: string]: any } | null;
    csrfToken: string;
}>();

const form = ref({
    name: "",
    slug: "",
    email: "",
    time_slot_interval: 30,
    accepts_online_bookings: 1,
    timezone: "Asia/Tokyo",
    cancellation_deadline_minutes: 1440,
    booking_deadline_minutes: 0,
});

const slugErrorMessages = ref<string[]>([]);
const slugSuccessMessages = ref<string[]>([]);
const isSlugUnique = ref(false); // Changed from isSlugValid to be explicit about uniqueness check
const isSlugChecked = ref(false);

// --- Validation ---
const rules = {
    required: (value: any) => !!(value || value === 0) || "必須項目です。",
    numeric: (value: string) =>
        /^(0|[1-9][0-9]*)$/.test(value) || "半角数字で入力してください。",
    slugFormat: (value: string) =>
        /^[a-z0-9-]+$/.test(value) ||
        "店舗 ID は半角英数字とハイフンのみ使用できます。",
    slugReserved: (value: string) =>
        !["create", "edit"].includes(value) ||
        "このIDは予約されているため使用できません。",
};

onMounted(() => {
    if (props.oldInput) {
        form.value.name = props.oldInput.name ?? "";
        form.value.slug = props.oldInput.slug ?? "";
        form.value.email = props.oldInput.email ?? "";
        form.value.time_slot_interval = props.oldInput.time_slot_interval ?? 30;
        form.value.accepts_online_bookings = props.oldInput.hasOwnProperty(
            "accepts_online_bookings"
        )
            ? Number(props.oldInput.accepts_online_bookings)
            : 1;
        form.value.cancellation_deadline_minutes =
            props.oldInput.cancellation_deadline_minutes ?? 1440;
        form.value.booking_deadline_minutes =
            props.oldInput.booking_deadline_minutes ?? 0;
        if (form.value.slug) {
            checkSlugUniqueness();
        }
    }
});

const checkSlugUniqueness = async () => {
    // Reset server messages
    slugErrorMessages.value = [];
    slugSuccessMessages.value = [];
    isSlugUnique.value = false;
    isSlugChecked.value = false;

    // Only proceed if basic rules pass
    if (
        rules.required(form.value.slug) !== true ||
        rules.slugFormat(form.value.slug) !== true ||
        rules.slugReserved(form.value.slug) !== true
    ) {
        return;
    }

    try {
        const response = await axios.get(
            `/owner/api/shops/validate-slug?slug=${form.value.slug}`
        );
        if (response.data.is_valid) {
            slugSuccessMessages.value = ["このIDは使用できます。"];
            isSlugUnique.value = true;
        } else {
            slugErrorMessages.value = [response.data.message];
        }
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            slugErrorMessages.value = [error.response.data.message];
        } else {
            slugErrorMessages.value = ["IDの検証中にエラーが発生しました。"];
        }
    }
    isSlugChecked.value = true;
};

const isFormValid = computed(() => {
    const nameValid = rules.required(form.value.name) === true;
    const emailValid = rules.required(form.value.email) === true;
    const slugFormatValid =
        rules.required(form.value.slug) === true &&
        rules.slugFormat(form.value.slug) === true &&
        rules.slugReserved(form.value.slug) === true;
    const intervalValid = rules.required(form.value.time_slot_interval) === true;
    const acceptsValid =
        rules.required(form.value.accepts_online_bookings) === true;

    const cancelStr = String(form.value.cancellation_deadline_minutes ?? "");
    const cancelValid =
        rules.required(cancelStr) === true && rules.numeric(cancelStr) === true;

    const bookingStr = String(form.value.booking_deadline_minutes ?? "");
    const bookingValid =
        rules.required(bookingStr) === true && rules.numeric(bookingStr) === true;

    return (
        nameValid &&
        emailValid &&
        slugFormatValid &&
        intervalValid &&
        acceptsValid &&
        cancelValid &&
        bookingValid &&
        isSlugChecked.value &&
        isSlugUnique.value
    );
});
</script>
