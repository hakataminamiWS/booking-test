<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn
                       :href="businessHoursIndexUrl"
                       prepend-icon="mdi-arrow-left"
                       variant="text">
                    営業時間一覧へ戻る
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
                    <v-card-title>特別休業日登録</v-card-title>
                    <v-card-text>
                        <form :action="formAction" method="POST">
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
                                          v-model="form.start_at"
                                          name="start_at"
                                          label="開始日 *"
                                          type="text"
                                          placeholder="YYYY-MM-DD"
                                          required
                                          :rules="dateRule"
                                          validate-on="lazy invalid-input"
                                          append-inner-icon="mdi-calendar"
                                          @click:append-inner="openStartDateDialog"></v-text-field>

                            <v-dialog v-model="startDateDialog" width="auto">
                                <v-date-picker
                                               v-model="startDateForPicker"
                                               @update:modelValue="
                                                updateStartDateFromPicker
                                            "
                                               title="開始日"></v-date-picker>
                            </v-dialog>

                            <v-text-field
                                          v-model="form.end_at"
                                          name="end_at"
                                          label="終了日 *"
                                          type="text"
                                          placeholder="YYYY-MM-DD"
                                          required
                                          :rules="dateRule"
                                          validate-on="lazy invalid-input"
                                          :error-messages="form.endDateError"
                                          append-inner-icon="mdi-calendar"
                                          @click:append-inner="openEndDateDialog"></v-text-field>

                            <v-dialog v-model="endDateDialog" width="auto">
                                <v-date-picker
                                               v-model="endDateForPicker"
                                               @update:modelValue="updateEndDateFromPicker"
                                               title="終了日"></v-date-picker>
                            </v-dialog>

                            <v-text-field
                                          v-model="form.name"
                                          name="name"
                                          label="休業日名"
                                          hint="（例：「夏季休業」）"
                                          persistent-hint></v-text-field>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn
                                       type="submit"
                                       color="primary"
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

const props = defineProps({
    shop: Object,
    csrfToken: String,
    errors: Array as () => string[],
    oldInput: Object,
});

const form = ref({
    name: "",
    start_at: "",
    end_at: "",
    endDateError: "",
});

const startDateDialog = ref(false);
const startDateForPicker = ref<Date | null>(null);
const endDateDialog = ref(false);
const endDateForPicker = ref<Date | null>(null);

onMounted(() => {
    if (props.oldInput) {
        form.value.name = props.oldInput.name ?? "";
        form.value.start_at = props.oldInput.start_at ?? "";
        form.value.end_at = props.oldInput.end_at ?? "";
    }
});

const formAction = computed(
    () => `/owner/shops/${props.shop.slug}/business-hours/special-closed-days`
);

const businessHoursIndexUrl = computed(
    () => `/owner/shops/${props.shop.slug}/business-hours`
);

const openStartDateDialog = () => {
    startDateForPicker.value = form.value.start_at
        ? new Date(form.value.start_at)
        : new Date();
    startDateDialog.value = true;
};

const updateStartDateFromPicker = () => {
    if (startDateForPicker.value) {
        const d = startDateForPicker.value;
        const year = d.getFullYear();
        const month = (d.getMonth() + 1).toString().padStart(2, "0");
        const day = d.getDate().toString().padStart(2, "0");
        form.value.start_at = `${year}-${month}-${day}`;
    }
    startDateDialog.value = false;
};

const openEndDateDialog = () => {
    endDateForPicker.value = form.value.end_at
        ? new Date(form.value.end_at)
        : new Date();
    endDateDialog.value = true;
};

const updateEndDateFromPicker = () => {
    if (endDateForPicker.value) {
        const d = endDateForPicker.value;
        const year = d.getFullYear();
        const month = (d.getMonth() + 1).toString().padStart(2, "0");
        const day = d.getDate().toString().padStart(2, "0");
        form.value.end_at = `${year}-${month}-${day}`;
    }
    endDateDialog.value = false;
};

// --- Validation ---
const dateRule = [
    (v: string) => !!v || "日付は必須です。",
    (v: string) =>
        /^\d{4}-\d{2}-\d{2}$/.test(v) || "YYYY-MM-DD 形式で入力してください。",
];

const validateForm = (event: Event) => {
    form.value.endDateError = "";
    if (
        form.value.start_at &&
        form.value.end_at &&
        form.value.start_at > form.value.end_at
    ) {
        form.value.endDateError = "終了日は開始日以降にしてください。";
        event.preventDefault();
    }
};
</script>
