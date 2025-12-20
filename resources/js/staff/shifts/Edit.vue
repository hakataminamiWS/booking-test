<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn
                       :href="`/shops/${shop.slug}/staff`"
                       prepend-icon="mdi-arrow-left"
                       variant="text">
                    店舗詳細へ戻る
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
                    <v-card-title>マイシフト編集:
                        {{ props.staff.profile.nickname }}</v-card-title>

                    <v-card-subtitle
                                     class="mt-2 d-flex justify-space-between align-center"
                                     :class="mobile ? 'flex-column' : 'justify-space-between'
                                        ">
                        <v-menu :close-on-content-click="false">
                            <template v-slot:activator="{ props: menuProps }">
                                <span
                                      v-bind="menuProps"
                                      class="cursor-pointer text-body-1 font-weight-bold">
                                    {{ formattedWeekStart }} 〜
                                    {{ formattedWeekEnd }}
                                    <v-icon small>mdi-calendar</v-icon>
                                </span>
                            </template>
                            <v-date-picker :model-value="targetDate" @update:model-value="handleDateChange"
                                           show-adjacent-months
                                           hide-header></v-date-picker>
                        </v-menu>
                        <div>
                            <v-btn :href="prevWeekUrl" variant="outlined" size="small" class="mr-2">前の週へ</v-btn>
                            <v-btn :href="nextWeekUrl" variant="outlined" size="small">次の週へ</v-btn>
                        </div>
                    </v-card-subtitle>

                    <v-card-text>
                        <v-alert v-if="props.errors && props.errors.length > 0" type="error" class="mb-4" closable>
                            <ul class="ml-4">
                                <li v-for="(error, index) in props.errors" :key="index">
                                    {{ error }}
                                </li>
                            </ul>
                        </v-alert>

                        <form :action="formAction" method="POST">
                            <v-text-field label="タイムゾーン" :model-value="props.shop.timezone" readonly disabled
                                          class="mb-4"></v-text-field>

                            <input type="hidden" name="_token" :value="csrfToken" />
                            <input type="hidden" name="_method" value="PUT" />
                            <input type="hidden" name="date" :value="date" />

                            <v-row v-for="(day, dayIndex) in weekSchedules" :key="day.formattedDate" align="center"
                                   class="my-2 pa-2 border rounded" :class="{
                                    'bg-grey-lighten-4':
                                        day.shopBusinessInfo.includes(
                                            '定休日'
                                        ) ||
                                        day.shopBusinessInfo.includes(
                                            '特別休業日'
                                        ),
                                }">
                                <v-col cols="12" md="3">
                                    <h3 class="text-h6">
                                        {{ day.formattedDate }}
                                    </h3>
                                    <span class="text-body-2 text-medium-emphasis">{{ day.shopBusinessInfo }}</span>
                                </v-col>
                                <v-col cols="12" md="9">
                                    <div v-if="day.isHoliday" class="text-center pa-4">
                                        <p class="text-medium-emphasis">休日</p>
                                        <input type="hidden" :name="`schedules[${dayIndex}][0][start_time]`"
                                               value="00:00" />
                                        <input type="hidden" :name="`schedules[${dayIndex}][0][end_time]`"
                                               value="00:00" />
                                    </div>
                                    <div v-else>
                                        <div v-for="(
schedule, scheduleIndex
                                            ) in day.schedules" :key="scheduleIndex" class="d-flex align-center my-2">
                                            <v-text-field label="開始時刻" v-model="schedule.start_time"
                                                          :name="`schedules[${dayIndex}][${scheduleIndex}][start_time]`"
                                                          type="time"
                                                          style="max-width: 150px" class="mr-2" :error-messages="schedule.startTimeError
                                                            " append-inner-icon="mdi-clock-outline"
                                                          @click:append-inner="
                                                            openDialog(
                                                                dayIndex,
                                                                scheduleIndex,
                                                                'start_time'
                                                            )
                                                            " />
                                            <v-text-field label="終了時刻" v-model="schedule.end_time"
                                                          :name="`schedules[${dayIndex}][${scheduleIndex}][end_time]`"
                                                          type="time"
                                                          style="max-width: 150px" class="mr-2" :error-messages="schedule.endTimeError
                                                            " append-inner-icon="mdi-clock-outline"
                                                          @click:append-inner="
                                                            openDialog(
                                                                dayIndex,
                                                                scheduleIndex,
                                                                'end_time'
                                                            )
                                                            " />
                                            <v-btn icon="mdi-close" size="x-small" variant="tonal" @click="
                                                removeSchedule(
                                                    dayIndex,
                                                    scheduleIndex
                                                )
                                                "></v-btn>
                                            <v-tooltip v-if="hasWarning(day, schedule)" text="店舗の営業情報と異なります"
                                                       location="top">
                                                <template v-slot:activator="{
                                                    props: tooltipProps,
                                                }">
                                                    <v-icon
                                                            v-bind="tooltipProps"
                                                            color="warning"
                                                            class="ml-2">mdi-alert-circle-outline</v-icon>
                                                </template>
                                            </v-tooltip>
                                        </div>
                                    </div>
                                    <v-btn size="default" @click="addSchedule(dayIndex)" class="mr-2">時間を追加</v-btn>
                                    <v-btn size="default" @click="setAsHoliday(dayIndex)"
                                           v-if="!day.isHoliday">休日にする</v-btn>
                                </v-col>
                            </v-row>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn type="submit" color="primary" @click="handleSubmit">この内容で保存する</v-btn>
                            </v-card-actions>
                        </form>
                    </v-card-text>
                </v-card>

                <v-dialog v-model="dialog" width="auto">
                    <v-card>
                        <v-time-picker v-model="currentTime" format="24hr"></v-time-picker>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="blue-darken-1" variant="text" @click="closeDialog">
                                キャンセル
                            </v-btn>
                            <v-btn color="blue-darken-1" variant="text" @click="saveTime">
                                OK
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { useDisplay } from "vuetify";
import {
    format,
    parseISO,
    startOfWeek,
    endOfWeek,
    eachDayOfInterval,
    subWeeks,
    addWeeks,
} from "date-fns";
import { formatInTimeZone } from "date-fns-tz";
import ShopHeader from "@/components/common/ShopHeader.vue";


// --- Type Definitions --- //

interface Schedule {
    start_time: string;
    end_time: string;
    startTimeError?: string;
    endTimeError?: string;
}

interface DaySchedule {
    formattedDate: string;
    isHoliday: boolean;
    schedules: Schedule[];
    shopBusinessInfo: string;
}

// --- Props --- //

const props = defineProps({
    shop: { type: Object, required: true },
    staff: { type: Object, required: true },
    schedules: { type: Array, required: true },
    businessHours: { type: Array, required: true },
    specialOpenDays: { type: Array, required: true },
    specialClosedDays: { type: Array, required: true },
    date: { type: String, required: true },
    csrfToken: { type: String, required: true },
    errors: { type: Array, default: () => [] },
});

const { mobile } = useDisplay();

// --- Form and URL Computations --- //

const formAction = computed(
    () => `/shops/${props.shop.slug}/staff/shifts`
);

const targetDate = parseISO(props.date);

const prevWeekUrl = computed(
    () => `?date=${format(subWeeks(targetDate, 1), "yyyy-MM-dd")}`
);

const nextWeekUrl = computed(
    () => `?date=${format(addWeeks(targetDate, 1), "yyyy-MM-dd")}`
);

// --- Date Computations --- //

const weekStartsOn = 0;

const weekInterval = {
    start: startOfWeek(targetDate, { weekStartsOn }),
    end: endOfWeek(targetDate, { weekStartsOn }),
};

const weekDays = eachDayOfInterval(weekInterval);

const dateOptions: Intl.DateTimeFormatOptions = {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    weekday: "short",
};

const formattedWeekStart = computed(() => {
    return weekInterval.start.toLocaleString(undefined, dateOptions);
});

const formattedWeekEnd = computed(() => {
    return weekInterval.end.toLocaleString(undefined, dateOptions);
});

// --- Business Hours Logic --- //

const getShopBusinessInfo = (date: Date): string => {
    const dateStr = format(date, "yyyy-MM-dd");
    const dayOfWeek = date.getDay();

    const specialClosed = props.specialClosedDays.find(
        (d) => dateStr >= d.start_at && dateStr <= d.end_at
    );

    if (specialClosed) return `特別休業日: ${specialClosed.name}`;

    const specialOpen = props.specialOpenDays.find((d) => d.date === dateStr);

    if (specialOpen)
        return `特別営業日: ${specialOpen.start_time.slice(
            0,
            5
        )} - ${specialOpen.end_time.slice(0, 5)}`;

    const regularHours = props.businessHours.find(
        (h) => h.day_of_week === dayOfWeek
    );

    if (regularHours && regularHours.is_open)
        return `通常営業: ${regularHours.start_time.slice(
            0,
            5
        )} - ${regularHours.end_time.slice(0, 5)}`;

    return "定休日";
};

// --- Reactive Data --- //

const weekSchedules = ref<DaySchedule[]>(
    weekDays.map((date) => {
        const dateStr = format(date, "yyyy-MM-dd");

        const existingSchedules = props.schedules
            .filter((s) => {
                const localDateStr = formatInTimeZone(
                    s.workable_start_at,
                    props.shop.timezone,
                    "yyyy-MM-dd"
                );
                return localDateStr === dateStr;
            })
            .map((s) => ({
                start_time: formatInTimeZone(
                    s.workable_start_at,
                    props.shop.timezone,
                    "HH:mm"
                ),
                end_time: formatInTimeZone(
                    s.workable_end_at,
                    props.shop.timezone,
                    "HH:mm"
                ),
                startTimeError: "",
                endTimeError: "",
            }));

        const isHoliday =
            existingSchedules.length === 1 &&
            existingSchedules[0].start_time === "00:00" &&
            existingSchedules[0].end_time === "00:00";

        return {
            formattedDate: date.toLocaleString(undefined, dateOptions),
            isHoliday: isHoliday,
            schedules: isHoliday ? [] : existingSchedules,
            shopBusinessInfo: getShopBusinessInfo(date),
        };
    })
);

// --- Methods --- //

const handleDateChange = (newDate: Date) => {
    window.location.href = `?date=${format(newDate, "yyyy-MM-dd")}`;
};

const addSchedule = (dayIndex: number) => {
    const day = weekSchedules.value[dayIndex];

    let newStartTime = "09:00";
    let newEndTime = "18:00";

    const businessInfo = day.shopBusinessInfo;

    if (businessInfo.includes("営業")) {
        const timeMatch = businessInfo.match(/(\d{2}:\d{2}) - (\d{2}:\d{2})/);
        if (timeMatch) {
            newStartTime = timeMatch[1];
            newEndTime = timeMatch[2];
        }
    } else if (businessInfo.includes("休日")) {
        newStartTime = "00:00";
        newEndTime = "00:00";
    }

    const newSchedule: Schedule = {
        start_time: newStartTime,
        end_time: newEndTime,
        startTimeError: "",
        endTimeError: "",
    };

    day.isHoliday = false;

    const holidayScheduleIndex = day.schedules.findIndex(
        (s) => s.start_time === "00:00" && s.end_time === "00:00"
    );

    if (holidayScheduleIndex > -1) {
        day.schedules.splice(holidayScheduleIndex, 1);
    }

    day.schedules.push(newSchedule);
};

const removeSchedule = (dayIndex: number, scheduleIndex: number) => {
    weekSchedules.value[dayIndex].schedules.splice(scheduleIndex, 1);
};

const setAsHoliday = (dayIndex: number) => {
    weekSchedules.value[dayIndex].schedules = [
        { start_time: "00:00", end_time: "00:00" },
    ];
    weekSchedules.value[dayIndex].isHoliday = true;
};

const hasWarning = (day: DaySchedule, schedule: Schedule): boolean => {
    if (
        day.shopBusinessInfo.includes("休業") ||
        day.shopBusinessInfo.includes("定休日")
    ) {
        return true;
    }

    if (day.shopBusinessInfo.includes("営業")) {
        const parts = day.shopBusinessInfo.match(
            /(\d{2}:\d{2}) - (\d{2}:\d{2})/
        );
        if (parts) {
            const [, shopStart, shopEnd] = parts;
            return (
                schedule.start_time < shopStart || schedule.end_time > shopEnd
            );
        }
    }

    return false;
};

// --- Dialog State ---

const dialog = ref(false);

const editingField = ref<{
    dayIndex: number;
    scheduleIndex: number;
    type: "start_time" | "end_time";
} | null>(null);

const currentTime = ref<string | null>(null);

const openDialog = (
    dayIndex: number,
    scheduleIndex: number,
    type: "start_time" | "end_time"
) => {
    editingField.value = { dayIndex, scheduleIndex, type };
    currentTime.value =
        weekSchedules.value[dayIndex].schedules[scheduleIndex][type];
    dialog.value = true;
};

const saveTime = () => {
    if (editingField.value) {
        const { dayIndex, scheduleIndex, type } = editingField.value;
        weekSchedules.value[dayIndex].schedules[scheduleIndex][type] =
            currentTime.value;
    }
    closeDialog();
};

const closeDialog = () => {
    dialog.value = false;
    editingField.value = null;
    currentTime.value = null;
};

// --- Validation ---

const validateForm = (): boolean => {
    let hasError = false;

    weekSchedules.value.forEach((day) => {
        day.schedules.forEach((s) => {
            s.startTimeError = "";
            s.endTimeError = "";
        });

        day.schedules.forEach((schedule) => {
            if (schedule.start_time > schedule.end_time) {
                schedule.endTimeError =
                    "終了時刻は開始時刻より後にしてください。";
                hasError = true;
            }
        });

        const sortedSchedules = [...day.schedules].sort((a, b) =>
            a.start_time.localeCompare(b.start_time)
        );

        for (let i = 1; i < sortedSchedules.length; i++) {
            const previous = sortedSchedules[i - 1];
            const current = sortedSchedules[i];

            if (previous.end_time > current.start_time) {
                const errorMsg = "時間が重複しています。";
                previous.endTimeError = errorMsg;
                current.startTimeError = errorMsg;
                hasError = true;
            }
        }
    });

    return !hasError;
};

const handleSubmit = (event: Event) => {
    if (!validateForm()) {
        event.preventDefault();
    }
};
</script>
