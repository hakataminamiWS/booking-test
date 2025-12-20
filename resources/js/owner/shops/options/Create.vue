<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn :href="optionsIndexUrl" prepend-icon="mdi-arrow-left">
                    オプション一覧に戻る
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
                    <v-card-title>オプション新規登録</v-card-title>
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

                            <v-text-field
                                          v-model="formData.name"
                                          name="name"
                                          label="オプション名"
                                          required
                                          :rules="[rules.required]"
                                          hint="お客様に表示されるオプションの正式名称を入力します。"
                                          persistent-hint
                                          class="mb-4"></v-text-field>

                            <v-text-field
                                          v-model="formData.price"
                                          @update:model-value="formData.price = formatNumericInput($event)"
                                          name="price"
                                          label="追加料金"
                                          :rules="[rules.required, rules.numeric]"
                                          inputmode="numeric"
                                          suffix="円"
                                          required
                                          hint="オプションの価格を円単位で入力します。"
                                          persistent-hint
                                          class="mb-4"></v-text-field>

                            <v-text-field
                                          v-model="formData.additional_duration"
                                          @update:model-value="formData.additional_duration = formatNumericInput($event)"
                                          name="additional_duration"
                                          label="追加所要時間"
                                          :rules="[rules.required, rules.numeric]"
                                          inputmode="numeric"
                                          suffix="分"
                                          required
                                          hint="サービスの所要時間に追加される時間を分単位で入力します。"
                                          persistent-hint
                                          class="mb-4"></v-text-field>

                            <v-textarea
                                        v-model="formData.description"
                                        name="description"
                                        label="オプションの説明"
                                        hint="お客様に表示されるオプションの詳細な説明を入力します。（任意）"
                                        persistent-hint
                                        class="mb-4"></v-textarea>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn
                                       type="submit"
                                       color="primary"
                                       :disabled="!isFormValid">登録する</v-btn>
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

const optionsIndexUrl = computed(() => `/owner/shops/${props.shop.slug}/options`);
const formAction = computed(() => `/owner/shops/${props.shop.slug}/options`);

const formData = ref({
    name: "",
    price: 0,
    additional_duration: 0,
    description: "",
});

onMounted(() => {
    if (props.oldInput && Object.keys(props.oldInput).length > 0) {
        formData.value.name = props.oldInput.name ?? "";
        formData.value.price = props.oldInput.price ?? 0;
        formData.value.additional_duration =
            props.oldInput.additional_duration ?? 0;
        formData.value.description = props.oldInput.description ?? "";
    }
});

const rules = {
    required: (value: any) => !!(value || value === 0) || "必須項目です。",
    numeric: (value: string) =>
        /^(0|[1-9][0-9]*)$/.test(value) || "半角数字で入力してください。",
};

const isFormValid = computed(() => {
    const priceStr = String(formData.value.price ?? "");
    const durationStr = String(formData.value.additional_duration ?? "");

    const nameValid = rules.required(formData.value.name) === true;
    const priceValid =
        rules.numeric(priceStr) === true && rules.required(priceStr) === true;
    const durationValid =
        rules.numeric(durationStr) === true &&
        rules.required(durationStr) === true;

    return nameValid && priceValid && durationValid;
});
</script>
