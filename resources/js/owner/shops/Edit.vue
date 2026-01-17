<template>
    <OwnerLayout :shop="props.shop" currentPage="edit">
        <v-container>
            <v-row>
                <v-col cols="12">
                    <v-card>
                        <v-card-title>店舗情報編集</v-card-title>
                        <v-divider></v-divider>
                        <v-card-text>
                            <form
                                  :action="`/owner/shops/${props.shop.slug}`"
                                  method="POST">
                                <input
                                       type="hidden"
                                       name="_token"
                                       :value="props.csrfToken" />
                                <input type="hidden" name="_method" value="PUT" />

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
                                              label="店舗 ID"
                                              readonly
                                              disabled></v-text-field>

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

                                <v-text-field v-model="form.timezone" label="タイムゾーン" readonly disabled></v-text-field>

                                <v-text-field v-model.number="form.cancellation_deadline_minutes
                                    " @update:model-value="
                                        form.cancellation_deadline_minutes =
                                        formatNumericInput($event) as any
                                        " name="cancellation_deadline_minutes" label="キャンセル期限（分前） *" required
                                              :rules="[rules.required, rules.numeric]" inputmode="numeric"
                                              hint="予約の何分前までお客様によるキャンセルを許可するか設定します。(例: 1440 分 = 24 時間前)"
                                              persistent-hint></v-text-field>

                                <v-text-field v-model.number="form.booking_deadline_minutes
                                    " @update:model-value="
                                        form.booking_deadline_minutes =
                                        formatNumericInput($event) as any
                                        " name="booking_deadline_minutes" label="予約締切（分前） *" required
                                              :rules="[rules.required, rules.numeric]" inputmode="numeric"
                                              hint="予約の何分前でオンライン予約の受付を締め切るか設定します。(0 は直前まで許可)"
                                              persistent-hint></v-text-field>

                                <v-card-actions>
                                    <v-btn color="error" @click="deleteDialog = true">削除する</v-btn>
                                    <v-spacer></v-spacer>
                                    <v-btn type="submit" color="primary" :disabled="!isFormValid">更新する</v-btn>
                                </v-card-actions>
                            </form>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Delete Dialog -->
            <v-dialog v-model="deleteDialog" max-width="600px">
                <v-card>
                    <v-card-title class="text-h5">店舗を削除しますか？</v-card-title>
                    <v-card-text>
                        <p>
                            この操作は元に戻せません。店舗を削除するには、以下の店舗名を入力してください。
                        </p>
                        <p class="font-weight-bold text-center my-4">
                            {{ props.shop.name }}
                        </p>
                        <v-text-field v-model="confirmationText" label="店舗名を入力" outlined></v-text-field>
                    </v-card-text>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn text @click="deleteDialog = false">キャンセル</v-btn>
                        <form :action="deleteUrl" method="POST">
                            <input type="hidden" name="_token" :value="props.csrfToken" />
                            <input type="hidden" name="_method" value="DELETE" />
                            <v-btn color="error" type="submit" :disabled="!isDeleteConfirmed">
                                削除を実行
                            </v-btn>
                        </form>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-container>
    </OwnerLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import OwnerLayout from "@/components/owner/OwnerLayout.vue";
import { formatNumericInput } from "@/composables/useNumericInput";

interface Shop {
    name: string;
    slug: string;
    email: string;
    time_slot_interval: number;
    booking_confirmation_type: string;
    accepts_online_bookings: boolean;
    timezone: string;
    cancellation_deadline_minutes: number;
    booking_deadline_minutes: number;
}

const props = defineProps<{
    shop: Shop;
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

onMounted(() => {
    const source =
        props.oldInput && Object.keys(props.oldInput).length > 0
            ? props.oldInput
            : props.shop;

    form.value.name = source.name ?? "";
    form.value.slug = props.shop.slug; // slug is readonly
    form.value.email = source.email ?? "";
    form.value.time_slot_interval = source.time_slot_interval ?? 30;
    form.value.accepts_online_bookings = source.hasOwnProperty(
        "accepts_online_bookings"
    )
        ? Number(source.accepts_online_bookings)
        : 1;
    form.value.timezone = props.shop.timezone; // timezone is readonly
    form.value.cancellation_deadline_minutes =
        source.cancellation_deadline_minutes ?? 1440;
    form.value.booking_deadline_minutes = source.booking_deadline_minutes ?? 0;
});

// --- URLs ---
const deleteUrl = computed(() => `/owner/shops/${props.shop.slug}`);

// --- Delete Dialog ---
const deleteDialog = ref(false);
const confirmationText = ref("");
const isDeleteConfirmed = computed(
    () => confirmationText.value === props.shop.name
);

// --- Validation ---
const rules = {
    required: (value: any) => !!(value || value === 0) || "必須項目です。",
    numeric: (value: string) =>
        /^(0|[1-9][0-9]*)$/.test(value) || "半角数字で入力してください。",
};

const isFormValid = computed(() => {
    const nameValid = rules.required(form.value.name) === true;
    const emailValid = rules.required(form.value.email) === true;
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
        acceptsValid &&
        cancelValid &&
        bookingValid
    );
});
</script>
