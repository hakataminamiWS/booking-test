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
                <ShopHeader :shop="shop" />
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title>メニュー編集</v-card-title>
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
                            <input type="hidden" name="_method" value="PUT" />

                            <v-text-field
                                          v-model="formData.name"
                                          name="name"
                                          label="メニュー名"
                                          required
                                          :rules="[rules.required]"
                                          hint="お客様に表示されるメニューの正式名称を入力します。"
                                          persistent-hint
                                          class="mb-4"></v-text-field>

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
                                          class="mb-4"></v-text-field>

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
                                          class="mb-4"></v-text-field>

                            <v-textarea
                                        v-model="formData.description"
                                        name="description"
                                        label="メニューの説明"
                                        hint="お客様に表示されるメニューの詳細な説明を入力します。（任意）"
                                        persistent-hint
                                        class="mb-4"></v-textarea>

                            <v-switch
                                      v-model="formData.requires_staff_assignment"
                                      :true-value="1"
                                      :false-value="0"
                                      label="担当者の割り当て"
                                      hint="このメニューの予約時に、担当スタッフの選択を必須にするかどうかを設定します。"
                                      persistent-hint
                                      inset
                                      color="primary"></v-switch>
                            <input
                                   type="hidden"
                                   name="requires_staff_assignment"
                                   :value="formData.requires_staff_assignment ? 1 : 0
                                    " />

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
                                      class="mb-4"></v-select>

                            <template
                                      v-for="staffId in formData.staff_ids"
                                      :key="staffId">
                                <input
                                       type="hidden"
                                       name="staff_ids[]"
                                       :value="staffId" />
                            </template>

                            <v-select v-model="formData.option_ids" :items="props.options" item-title="name"
                                      item-value="id" label="関連オプション"
                                      multiple chips closable-chips hint="このメニューに適用可能なオプションをすべて選択してください。"
                                      persistent-hint class="mb-4"></v-select>

                            <template v-for="optionId in formData.option_ids" :key="optionId">
                                <input
                                       type="hidden"
                                       name="option_ids[]"
                                       :value="optionId" />
                            </template>

                            <v-switch v-model="formData.requires_cancellation_deadline
                                " :true-value="1" :false-value="0" label="特別なキャンセル期限"
                                      hint="店舗の基本設定とは異なるキャンセル期限を設定する場合にオンにします。" persistent-hint inset
                                      color="primary"></v-switch>
                            <input type="hidden" name="requires_cancellation_deadline" :value="formData.requires_cancellation_deadline
                                ? 1
                                : 0
                                " />

                            <v-text-field v-if="formData.requires_cancellation_deadline"
                                          v-model="formData.cancellation_deadline_minutes"
                                          @update:model-value="formData.cancellation_deadline_minutes = formatNumericInput($event)
                                            " name="cancellation_deadline_minutes" label="キャンセル期限"
                                          :rules="[rules.required, rules.numeric]" inputmode="numeric" suffix="分前"
                                          hint="予約の何分前までお客様によるキャンセルを許可するか設定します。"
                                          persistent-hint class="mb-4"></v-text-field>

                            <v-switch v-model="formData.requires_booking_deadline" :true-value="1" :false-value="0"
                                      label="特別な予約締切"
                                      hint="店舗の基本設定とは異なる予約締切を設定する場合にオンにします。" persistent-hint inset
                                      color="primary"></v-switch>
                            <input type="hidden" name="requires_booking_deadline" :value="formData.requires_booking_deadline ? 1 : 0
                                " />

                            <v-text-field v-if="formData.requires_booking_deadline"
                                          v-model="formData.booking_deadline_minutes" @update:model-value="formData.booking_deadline_minutes = formatNumericInput($event)
                                            " name="booking_deadline_minutes" label="予約締切"
                                          :rules="[rules.required, rules.numeric]" inputmode="numeric" suffix="分前"
                                          hint="予約の何分前でオンライン予約の受付を締め切るか設定します。"
                                          persistent-hint class="mb-4"></v-text-field>

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

        <v-dialog v-model="deleteDialog" max-width="500px">
            <v-card>
                <v-card-title class="text-h5">本当に削除しますか？</v-card-title>
                <v-card-text>この操作は元に戻せません。</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue-darken-1" variant="text" @click="deleteDialog = false">キャンセル</v-btn>
                    <form :action="deleteFormAction" method="POST" style="display: inline">
                        <input type="hidden" name="_token" :value="csrfToken" />
                        <input type="hidden" name="_method" value="DELETE" />
                        <v-btn color="error" variant="text" type="submit">削除する</v-btn>
                    </form>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import ShopHeader from "@/components/common/ShopHeader.vue";
import { formatNumericInput } from "@/composables/useNumericInput";

const deleteDialog = ref(false);

const props = defineProps({
    shop: { type: Object, required: true },
    menu: { type: Object, required: true },
    staffs: { type: Array, required: true },
    options: { type: Array, required: true },
    csrfToken: { type: String, required: true },
    errors: { type: Array, default: () => [] },
    oldInput: { type: Object, default: () => ({}) },
});

const menusIndexUrl = computed(() => `/owner/shops/${props.shop.slug}/menus`);
const formAction = computed(
    () => `/owner/shops/${props.shop.slug}/menus/${props.menu.id}`
);
const deleteFormAction = computed(
    () => `/owner/shops/${props.shop.slug}/menus/${props.menu.id}`
);

const formData = ref({
    name: props.menu.name,
    price: props.menu.price,
    duration: props.menu.duration,
    description: props.menu.description,
    requires_staff_assignment: props.menu.requires_staff_assignment,
    requires_cancellation_deadline: props.menu.requires_cancellation_deadline,
    cancellation_deadline_minutes: props.menu.cancellation_deadline_minutes,
    requires_booking_deadline: props.menu.requires_booking_deadline,
    booking_deadline_minutes: props.menu.booking_deadline_minutes,
    staff_ids: props.menu.staffs.map((staff: any) => staff.id),
    option_ids: props.menu.options.map((option: any) => option.id),
});

onMounted(() => {
    if (props.oldInput && Object.keys(props.oldInput).length > 0) {
        formData.value.name = props.oldInput.name ?? props.menu.name;
        formData.value.price = props.oldInput.price ?? props.menu.price;
        formData.value.duration = props.oldInput.duration ?? props.menu.duration;
        formData.value.description =
            props.oldInput.description ?? props.menu.description;
        formData.value.requires_staff_assignment =
            props.oldInput.requires_staff_assignment ??
            props.menu.requires_staff_assignment;
        formData.value.requires_cancellation_deadline =
            props.oldInput.requires_cancellation_deadline ??
            props.menu.requires_cancellation_deadline;
        formData.value.cancellation_deadline_minutes =
            props.oldInput.cancellation_deadline_minutes ??
            props.menu.cancellation_deadline_minutes;
        formData.value.requires_booking_deadline =
            props.oldInput.requires_booking_deadline ??
            props.menu.requires_booking_deadline;
        formData.value.booking_deadline_minutes =
            props.oldInput.booking_deadline_minutes ??
            props.menu.booking_deadline_minutes;
        formData.value.staff_ids =
            props.oldInput.staff_ids ??
            props.menu.staffs.map((staff: any) => staff.id);
        formData.value.option_ids =
            props.oldInput.option_ids ??
            props.menu.options.map((option: any) => option.id);
    }
});

const rules = {
    required: (value: any) => !!(value || value === 0) || "必須項目です。",
    numeric: (value: string) =>
        /^(0|[1-9][0-9]*)$/.test(value) || "半角数字で入力してください。",
};

const isFormValid = computed(() => {
    const priceStr = String(formData.value.price ?? "");
    const durationStr = String(formData.value.duration ?? "");
    const nameValid = rules.required(formData.value.name) === true;
    const priceValid =
        rules.required(priceStr) === true && rules.numeric(priceStr) === true;
    const durationValid =
        rules.required(durationStr) === true &&
        rules.numeric(durationStr) === true;

    let deadlineValid = true;
    if (formData.value.requires_cancellation_deadline) {
        const cancelStr = String(
            formData.value.cancellation_deadline_minutes ?? ""
        );
        deadlineValid =
            deadlineValid &&
            rules.required(cancelStr) === true &&
            rules.numeric(cancelStr) === true;
    }
    if (formData.value.requires_booking_deadline) {
        const bookingStr = String(
            formData.value.booking_deadline_minutes ?? ""
        );
        deadlineValid =
            deadlineValid &&
            rules.required(bookingStr) === true &&
            rules.numeric(bookingStr) === true;
    }

    return nameValid && priceValid && durationValid && deadlineValid;
});
</script>
