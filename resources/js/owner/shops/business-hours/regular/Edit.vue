<template>
    <OwnerLayout :shop="shop" currentPage="business-hours">
        <v-container>

            <v-row>
                <v-col cols="12">
                    <v-card>
                        <v-card-title>営業時間・定休日設定</v-card-title>
                        <v-card-text>
                            <v-alert
                                     type="info"
                                     variant="tonal"
                                     class="mb-4"
                                     icon="mdi-information">
                                <div class="text-caption text-sm-body-2">
                                    <p class="font-weight-bold mb-2">【営業時間・定休日の設定について】</p>
                                    <ul class="ml-4">
                                        <li>
                                            本設定は、シフト登録時に参考となるものです。
                                        </li>
                                        <li>
                                            予約枠の計算には影響しません（予約枠は、シフト登録内容および予約状況によって計算されます）。
                                        </li>
                                        <li>
                                            <strong>営業日：</strong>
                                            チェックを入れると、開始時刻、および終了時刻を設定できます。
                                        </li>
                                        <li>
                                            <strong>開始時間、終了時間：</strong>
                                            シフト登録時のデフォルトとして表示されます。時間外にシフトを登録する際に、警告を表示します（警告を無視して登録可能です）。
                                        </li>
                                    </ul>
                                </div>
                            </v-alert>

                            <form :action="formAction" method="POST">
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
                                              label="タイムゾーン"
                                              :model-value="shop.timezone"
                                              readonly
                                              disabled></v-text-field>

                                <v-table>
                                    <thead>
                                        <tr>
                                            <th>曜日</th>
                                            <th>営業日</th>
                                            <th>開始時刻</th>
                                            <th>終了時刻</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
item, index
                                        ) in form.business_hours"
                                            :key="item.day_of_week">
                                            <td>
                                                <input
                                                       type="hidden"
                                                       :name="`business_hours[${index}][day_of_week]`"
                                                       :value="item.day_of_week" />
                                                {{ dayOfWeek[item.day_of_week] }}
                                            </td>
                                            <td>
                                                <input
                                                       type="hidden"
                                                       :name="`business_hours[${index}][is_open]`"
                                                       :value="item.is_open ? 1 : 0" />
                                                <v-checkbox
                                                            v-model="item.is_open"></v-checkbox>
                                            </td>
                                            <td>
                                                <v-text-field
                                                              type="time"
                                                              v-model="item.start_time"
                                                              :name="`business_hours[${index}][start_time]`"
                                                              :disabled="!item.is_open"
                                                              :required="item.is_open"
                                                              style="max-width: 200px; min-width: 180px;"
                                                              append-inner-icon="mdi-clock-outline"
                                                              @click:append-inner="
                                                                openDialog(
                                                                    index,
                                                                    'start_time'
                                                                )
                                                                "></v-text-field>
                                            </td>
                                            <td>
                                                <v-text-field
                                                              type="time"
                                                              v-model="item.end_time"
                                                              :name="`business_hours[${index}][end_time]`"
                                                              :disabled="!item.is_open"
                                                              :required="item.is_open"
                                                              :error-messages="item.endTimeError
                                                                "
                                                              style="max-width: 200px; min-width: 180px;"
                                                              append-inner-icon="mdi-clock-outline"
                                                              @click:append-inner="
                                                                openDialog(
                                                                    index,
                                                                    'end_time'
                                                                )
                                                                "></v-text-field>
                                            </td>
                                        </tr>
                                    </tbody>
                                </v-table>

                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn
                                           type="submit"
                                           color="primary"
                                           @click="validateEndTime">設定する</v-btn>
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
    timezone?: string;
}

interface BusinessHour {
    day_of_week: number;
    is_open: boolean | number;
    start_time: string | null;
    end_time: string | null;
}

const props = withDefaults(defineProps<{
    shop: Shop;
    businessHours: BusinessHour[];
    csrfToken: string;
    errors?: string[];
    oldInput?: Record<string, any>;
}>(), {
    errors: () => [],
    oldInput: () => ({}),
});

const dayOfWeek = ["日", "月", "火", "水", "木", "金", "土"];

const form = ref<{ business_hours: any[] }>({
    business_hours: [],
});

onMounted(() => {
    const source =
        props.oldInput &&
            Object.keys(props.oldInput).length > 0 &&
            props.oldInput.business_hours
            ? props.oldInput.business_hours
            : props.businessHours;

    form.value.business_hours = source.map((bh: any) => ({
        ...bh,
        is_open: Boolean(Number(bh.is_open)),
        endTimeError: "",
    }));
});

const formAction = computed(
    () => `/owner/shops/${props.shop.slug}/business-hours/regular`
);

const businessHoursIndexUrl = computed(
    () => `/owner/shops/${props.shop.slug}/business-hours`
);

// --- Validation ---
const validateEndTime = (event: Event) => {
    let hasError = false;
    form.value.business_hours.forEach((item) => {
        item.endTimeError = "";
        if (
            item.is_open &&
            item.start_time &&
            item.end_time &&
            item.start_time >= item.end_time
        ) {
            item.endTimeError = "終了時刻は開始時刻より後にしてください。";
            hasError = true;
        }
    });

    if (hasError) {
        event.preventDefault();
    }
};

// --- Dialog State ---
const dialog = ref(false);
const editingField = ref<{
    dayIndex: number;
    type: "start_time" | "end_time";
} | null>(null);
const currentTime = ref<string | null>(null);

const openDialog = (dayIndex: number, type: "start_time" | "end_time") => {
    editingField.value = { dayIndex, type };
    currentTime.value = form.value.business_hours[dayIndex][type];
    dialog.value = true;
};

const saveTime = () => {
    if (editingField.value) {
        const { dayIndex, type } = editingField.value;
        form.value.business_hours[dayIndex][type] = currentTime.value;
    }
    closeDialog();
};

const closeDialog = () => {
    dialog.value = false;
    editingField.value = null;
    currentTime.value = null;
};
</script>
