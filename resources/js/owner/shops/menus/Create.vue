<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn :href="menusIndexUrl" prepend-icon="mdi-arrow-left">
                    メニュー一覧に戻る
                </v-btn>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="props.shop" />

                <v-card class="mt-4">
                    <v-card-title>メニュー新規登録</v-card-title>
                    <v-card-text>
                        <v-alert
                            v-if="props.errors.length > 0"
                            type="error"
                            class="mb-4"
                            closable
                        >
                            <ul>
                                <li
                                    v-for="(error, index) in props.errors"
                                    :key="index"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                        </v-alert>

                        <form :action="formAction" method="POST">
                            <input
                                type="hidden"
                                name="_token"
                                :value="csrfToken"
                            />

                            <v-text-field
                                v-model="formData.name"
                                name="name"
                                label="メニュー名"
                                required
                                hint="お客様に表示されるメニューの正式名称を入力します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-text-field
                                v-model="formData.price"
                                @update:model-value="formData.price = formatNumericInput($event)"
                                name="price"
                                label="価格"
                                :rules="[rules.required, rules.numeric]"
                                inputmode="numeric"
                                suffix="円"
                                required
                                hint="メニューの価格を円単位で入力します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-text-field
                                v-model="formData.duration"
                                @update:model-value="formData.duration = formatNumericInput($event)"
                                name="duration"
                                label="所要時間"
                                :rules="[rules.required, rules.numeric]"
                                inputmode="numeric"
                                suffix="分"
                                required
                                hint="サービスの所要時間を分単位で入力します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-textarea
                                v-model="formData.description"
                                name="description"
                                label="メニューの説明"
                                hint="お客様に表示されるメニューの詳細な説明を入力します。（任意）"
                                persistent-hint
                                class="mb-4"
                            ></v-textarea>

                            <v-switch
                                v-model="formData.requires_staff_assignment"
                                :true-value="1"
                                :false-value="0"
                                name="requires_staff_assignment"
                                label="担当者の割り当て"
                                hint="このメニューの予約時に、担当スタッフの選択を必須にするかどうかを設定します。"
                                persistent-hint
                                inset
                                color="primary"
                            ></v-switch>
                            <input
                                type="hidden"
                                name="requires_staff_assignment"
                                :value="
                                    formData.requires_staff_assignment ? 1 : 0
                                "
                            />

                            <v-select
                                v-model="formData.staff_ids"
                                :items="props.staffs"
                                item-title="profile.nickname"
                                item-value="id"
                                label="担当スタッフ"
                                multiple
                                chips
                                closable-chips
                                hint="このメニューを担当できるスタッフをすべて選択してください。"
                                persistent-hint
                                class="mb-4"
                            ></v-select>

                            <template
                                v-for="staffId in formData.staff_ids"
                                :key="staffId"
                            >
                                <input
                                    type="hidden"
                                    name="staff_ids[]"
                                    :value="staffId"
                                />
                            </template>

                            <v-select
                                v-model="formData.option_ids"
                                :items="props.options"
                                item-title="name"
                                item-value="id"
                                label="関連オプション"
                                multiple
                                chips
                                closable-chips
                                hint="このメニューに適用可能なオプションをすべて選択してください。"
                                persistent-hint
                                class="mb-4"
                            ></v-select>

                            <template
                                v-for="optionId in formData.option_ids"
                                :key="optionId"
                            >
                                <input
                                    type="hidden"
                                    name="option_ids[]"
                                    :value="optionId"
                                />
                            </template>

                            <v-switch
                                v-model="
                                    formData.requires_cancellation_deadline
                                "
                                :true-value="1"
                                :false-value="0"
                                name="requires_cancellation_deadline"
                                label="特別なキャンセル期限"
                                hint="店舗の基本設定とは異なるキャンセル期限を設定する場合にオンにします。"
                                persistent-hint
                                inset
                                color="primary"
                            ></v-switch>
                            <input
                                type="hidden"
                                name="requires_cancellation_deadline"
                                :value="
                                    formData.requires_cancellation_deadline
                                        ? 1
                                        : 0
                                "
                            />

                            <v-text-field
                                v-if="formData.requires_cancellation_deadline"
                                v-model="formData.cancellation_deadline_minutes"
                                @update:model-value="formData.cancellation_deadline_minutes = formatNumericInput($event)
                                "
                                name="cancellation_deadline_minutes"
                                label="キャンセル期限"
                                :rules="[rules.required, rules.numeric]"
                                inputmode="numeric"
                                suffix="分前"
                                hint="予約の何分前までお客様によるキャンセルを許可するか設定します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-switch
                                v-model="formData.requires_booking_deadline"
                                :true-value="1"
                                :false-value="0"
                                name="requires_booking_deadline"
                                label="特別な予約締切"
                                hint="店舗の基本設定とは異なる予約締切を設定する場合にオンにします。"
                                persistent-hint
                                inset
                                color="primary"
                            ></v-switch>
                            <input
                                type="hidden"
                                name="requires_booking_deadline"
                                :value="
                                    formData.requires_booking_deadline ? 1 : 0
                                "
                            />

                            <v-text-field
                                v-if="formData.requires_booking_deadline"
                                v-model="formData.booking_deadline_minutes"
                                @update:model-value="formData.booking_deadline_minutes = formatNumericInput($event)"
                                name="booking_deadline_minutes"
                                label="予約締切"
                                :rules="[rules.required, rules.numeric]"
                                inputmode="numeric"
                                suffix="分前"
                                hint="予約の何分前でオンライン予約の受付を締め切るか設定します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn
                                    type="submit"
                                    color="primary"
                                    @click="validateAndSubmit"
                                    >登録する</v-btn
                                >
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
import ShopHeader from "@/owner/shops/components/ShopHeader.vue";
import { formatNumericInput } from "@/composables/useNumericInput";

const props = defineProps({
    shop: { type: Object, required: true },
    staffs: { type: Array, required: true },
    options: { type: Array, required: true },
    csrfToken: { type: String, required: true },
    errors: { type: Array, default: () => [] },
    oldInput: { type: Object, default: () => ({}) },
});

const menusIndexUrl = computed(() => `/owner/shops/${props.shop.slug}/menus`);
const formAction = computed(() => `/owner/shops/${props.shop.slug}/menus`);

const formData = ref({
    name: "",
    price: 0,
    duration: 30,
    description: "",
    requires_staff_assignment: 0,
    requires_cancellation_deadline: 0,
    cancellation_deadline_minutes: 0,
    requires_booking_deadline: 0,
    booking_deadline_minutes: 0,
    staff_ids: [],
    option_ids: [],
});

onMounted(() => {
    if (props.oldInput && Object.keys(props.oldInput).length > 0) {
        formData.value.name = props.oldInput.name ?? "";
        formData.value.price = props.oldInput.price ?? 0;
        formData.value.duration = props.oldInput.duration ?? 30;
        formData.value.description = props.oldInput.description ?? "";
        formData.value.requires_staff_assignment =
            props.oldInput.requires_staff_assignment ?? 0;
        formData.value.requires_cancellation_deadline =
            props.oldInput.requires_cancellation_deadline ?? 0;
        formData.value.cancellation_deadline_minutes =
            props.oldInput.cancellation_deadline_minutes ?? 0;
        formData.value.requires_booking_deadline =
            props.oldInput.requires_booking_deadline ?? 0;
        formData.value.booking_deadline_minutes =
            props.oldInput.booking_deadline_minutes ?? 0;
        formData.value.staff_ids = props.oldInput.staff_ids ?? [];
        formData.value.option_ids = props.oldInput.option_ids ?? [];
    }
});

const rules = {
    required: (value: any) => !!(value || value === 0) || "必須項目です。",
    numeric: (value: string) =>
        /^(0|[1-9][0-9]*)$/.test(value) || "半角数字で入力してください。",
};

const validateAndSubmit = (event: Event) => {
    const fieldsToValidate = [
        String(formData.value.price ?? ""),
        String(formData.value.duration ?? ""),
    ];

    if (formData.value.requires_cancellation_deadline) {
        fieldsToValidate.push(
            String(formData.value.cancellation_deadline_minutes ?? "")
        );
    }
    if (formData.value.requires_booking_deadline) {
        fieldsToValidate.push(
            String(formData.value.booking_deadline_minutes ?? "")
        );
    }

    for (const val of fieldsToValidate) {
        if (rules.required(val) !== true || rules.numeric(val) !== true) {
            event.preventDefault();
            return;
        }
    }
};
</script>
