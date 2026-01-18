<template>
    <OwnerLayout :shop="shop" currentPage="business-hours">
        <v-container>

            <v-row>
                <v-col cols="12">
                    <v-card>
                        <v-card-title>特別営業日編集</v-card-title>
                        <v-card-text>
                            <form :action="formAction" method="POST">
                                <input
                                       type="hidden"
                                       name="_token"
                                       :value="props.csrfToken" />
                                <input type="hidden" name="_method" value="PUT" />

                                <v-alert
                                         type="info"
                                         variant="tonal"
                                         class="mb-4"
                                         icon="mdi-information">
                                    <div class="text-caption text-sm-body-2">
                                        <p class="font-weight-bold mb-2">【特別営業日の設定について】</p>
                                        <ul class="ml-4">
                                            <li>
                                                通常休業日のシフト登録時に、警告が出なくなります（日付、開始時刻、終了時刻の指定が可能です）。
                                            </li>
                                        </ul>
                                    </div>
                                </v-alert>

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
                                              v-model="form.date"
                                              name="date"
                                              label="日付 *"
                                              type="text"
                                              placeholder="YYYY-MM-DD"
                                              required
                                              :rules="dateRule"
                                              validate-on="lazy invalid-input"
                                              append-inner-icon="mdi-calendar"
                                              @click:append-inner="openDateDialog"></v-text-field>

                                <v-dialog v-model="dateDialog" width="auto">
                                    <v-date-picker
                                                   v-model="dateForPicker"
                                                   @update:modelValue="updateDateFromPicker"
                                                   title="日付"></v-date-picker>
                                </v-dialog>

                                <v-text-field
                                              v-model="form.start_time"
                                              name="start_time"
                                              label="開始時刻 *"
                                              type="time"
                                              required
                                              style="max-width: 200px; min-width: 180px;"
                                              append-inner-icon="mdi-clock-outline"
                                              @click:append-inner="openDialog('start_time')"></v-text-field>

                                <v-text-field
                                              v-model="form.end_time"
                                              name="end_time"
                                              label="終了時刻 *"
                                              type="time"
                                              required
                                              :error-messages="form.endTimeError"
                                              style="max-width: 200px; min-width: 180px;"
                                              append-inner-icon="mdi-clock-outline"
                                              @click:append-inner="openDialog('end_time')"></v-text-field>

                                <v-text-field
                                              v-model="form.name"
                                              name="name"
                                              label="営業日名"
                                              hint="（例：「祝日特別営業」）"
                                              persistent-hint></v-text-field>

                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn
                                           type="submit"
                                           color="primary"
                                           @click="validateForm">更新する</v-btn>
                                </v-card-actions>
                            </form>
                        </v-card-text>
                    </v-card>

                    <v-dialog v-model="dialog" width="auto">
                        <v-card>
                            <v-time-picker
                                           v-model="currentTime"
                                           format="24hr"></v-time-picker>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn
                                       color="blue-darken-1"
                                       variant="text"
                                       @click="closeDialog">
                                    キャンセル
                                </v-btn>
                                <v-btn
                                       color="blue-darken-1"
                                       variant="text"
                                       @click="saveTime">
                                    OK
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
                </v-col>
            </v-row>
        </v-container>
    </OwnerLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, type PropType } from "vue";
import OwnerLayout from "@/components/owner/OwnerLayout.vue";

interface Shop {
    name: string;
    slug: string;
}

interface SpecialOpenDay {
    id: number;
    name: string | null;
    date: string;
    start_time: string;
    end_time: string;
}

const props = withDefaults(defineProps<{
    shop: Shop;
    specialOpenDay: SpecialOpenDay;
    csrfToken: string;
    errors?: string[];
    oldInput?: Record<string, any>;
}>(), {
    errors: () => [],
    oldInput: () => ({}),
});

const form = ref({
    name: "",
    date: "",
    start_time: "",
    end_time: "",
    endTimeError: "",
});

onMounted(() => {
    if (props.oldInput && Object.keys(props.oldInput).length > 0) {
        form.value.name = props.oldInput.name ?? "";
        form.value.date = props.oldInput.date ?? "";
        form.value.start_time = props.oldInput.start_time ?? "";
        form.value.end_time = props.oldInput.end_time ?? "";
    } else if (props.specialOpenDay) {
        form.value.name = props.specialOpenDay.name ?? "";
        form.value.date = props.specialOpenDay.date;
        form.value.start_time = props.specialOpenDay.start_time;
        form.value.end_time = props.specialOpenDay.end_time;
    }
});

const formAction = computed(
    () =>
        `/owner/shops/${props.shop.slug}/business-hours/special-open-days/${props.specialOpenDay.id}`
);

const businessHoursIndexUrl = computed(
    () => `/owner/shops/${props.shop.slug}/business-hours`
);

// --- Validation ---
const dateRule = [
    (v: string) => !!v || "日付は必須です。",
    (v: string) =>
        /^\d{4}-\d{2}-\d{2}$/.test(v) || "YYYY-MM-DD 形式で入力してください。",
];

const validateForm = (event: Event) => {
    form.value.endTimeError = "";
    if (
        form.value.start_time &&
        form.value.end_time &&
        form.value.start_time >= form.value.end_time
    ) {
        form.value.endTimeError = "終了時刻は開始時刻より後にしてください。";
        event.preventDefault();
    }
};

// --- Time Dialog State ---
const dialog = ref(false);
const editingField = ref<"start_time" | "end_time" | null>(null);
const currentTime = ref<string | null>(null);

const openDialog = (type: "start_time" | "end_time") => {
    editingField.value = type;
    currentTime.value = form.value[type];
    dialog.value = true;
};

const saveTime = () => {
    if (editingField.value && currentTime.value !== null) {
        form.value[editingField.value] = currentTime.value;
    }
    closeDialog();
};

const closeDialog = () => {
    dialog.value = false;
    editingField.value = null;
    currentTime.value = null;
};

// --- Date Dialog State ---
const dateDialog = ref(false);
const dateForPicker = ref<Date | null>(null);

const openDateDialog = () => {
    dateForPicker.value = form.value.date
        ? new Date(form.value.date)
        : new Date();
    dateDialog.value = true;
};

const updateDateFromPicker = () => {
    if (dateForPicker.value) {
        const d = dateForPicker.value;
        const year = d.getFullYear();
        const month = (d.getMonth() + 1).toString().padStart(2, "0");
        const day = d.getDate().toString().padStart(2, "0");
        form.value.date = `${year}-${month}-${day}`;
    }
    dateDialog.value = false;
};
</script>
