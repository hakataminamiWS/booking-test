<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn
                    :href="staffsIndexUrl"
                    prepend-icon="mdi-arrow-left"
                    variant="text"
                >
                    スタッフ一覧に戻る
                </v-btn>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="props.shop" />

                <v-card class="mt-4">
                    <v-card-title
                        class="d-flex align-center"
                        :class="
                            mobile ? 'flex-column' : 'justify-space-between'
                        "
                    >
                        <span>シフト一覧</span>
                        <div
                            class="d-flex align-center"
                            :class="{ 'mt-2': mobile }"
                        >
                            <v-btn :href="prevMonthUrl" icon variant="text">
                                <v-icon>mdi-chevron-left</v-icon>
                            </v-btn>
                            <span class="mx-4 text-h6">{{
                                formattedMonth
                            }}</span>
                            <v-btn :href="nextMonthUrl" icon variant="text">
                                <v-icon>mdi-chevron-right</v-icon>
                            </v-btn>
                        </div>
                    </v-card-title>
                    <v-card-text>
                        <v-table>
                            <thead>
                                <tr>
                                    <th class="text-left">週</th>
                                    <th
                                        v-for="staff in staffs"
                                        :key="staff.id"
                                        class="text-center"
                                    >
                                        {{ staff.profile?.nickname || "N/A" }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in weeksWithShiftStatus"
                                    :key="item.week.start"
                                >
                                    <td>
                                        {{ formatWeekRange(item.week) }}
                                    </td>
                                    <td
                                        v-for="staff in staffs"
                                        :key="staff.id"
                                        class="text-center pa-0"
                                    >
                                        <a
                                            :href="
                                                getShiftEditUrl(
                                                    staff.id,
                                                    format(
                                                        parseISO(
                                                            item.week.start
                                                        ),
                                                        'yyyy-MM-dd'
                                                    )
                                                )
                                            "
                                            class="d-block pa-4 text-decoration-none"
                                        >
                                            <span
                                                :class="`text-${getStatusColor(
                                                    item.statuses[staff.id]
                                                )}`"
                                            >
                                                {{
                                                    item.statuses[staff.id] ===
                                                    "entered"
                                                        ? "入力済み"
                                                        : "未入力"
                                                }}
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useDisplay } from "vuetify";
import { format, parseISO, addMonths, subMonths } from "date-fns";
import { ja } from "date-fns/locale";
import { formatInTimeZone } from "date-fns-tz";
import ShopHeader from "@/owner/shops/components/ShopHeader.vue";

// --- Type Definitions ---
interface Shop {
    id: number;
    name: string;
    slug: string;
    timezone: string;
}

interface StaffProfile {
    nickname: string;
}

interface Staff {
    id: number;
    profile: StaffProfile | null;
}

interface WeekHeader {
    start: string;
    end: string;
}

interface WeekWithShiftStatus {
    week: WeekHeader;
    statuses: { [key: number]: "entered" | "not_entered" };
}

// --- Props ---
const props = defineProps<{
    shop: Shop;
    targetMonth: string;
    staffs: Staff[];
    weeksWithShiftStatus: WeekWithShiftStatus[];
}>();

const { mobile } = useDisplay();

// --- URL Computations ---
const staffsIndexUrl = computed(() => `/owner/shops/${props.shop.slug}/staffs`);
const targetMonthDate = computed(() => parseISO(props.targetMonth));

const prevMonthUrl = computed(() => {
    const prevMonth = subMonths(targetMonthDate.value, 1);
    return `?month=${format(prevMonth, "yyyy-MM")}`;
});

const nextMonthUrl = computed(() => {
    const nextMonth = addMonths(targetMonthDate.value, 1);
    return `?month=${format(nextMonth, "yyyy-MM")}`;
});

const getShiftEditUrl = (staffId: number, weekStartDate: string) => {
    return `/owner/shops/${props.shop.slug}/staffs/${staffId}/shifts?date=${weekStartDate}`;
};

// --- Date Formatting ---
const formattedMonth = computed(() => {
    return format(targetMonthDate.value, "yyyy年MM月");
});

const formatWeekRange = (week: WeekHeader): string => {
    const startDate = parseISO(week.start);
    const endDate = parseISO(week.end);
    if (mobile.value) {
        return `${formatInTimeZone(
            startDate,
            props.shop.timezone,
            "MM/dd (eee)",
            { locale: ja }
        )} 〜`;
    } else {
        return `${formatInTimeZone(
            startDate,
            props.shop.timezone,
            "MM/dd"
        )} (日) 〜 ${formatInTimeZone(
            endDate,
            props.shop.timezone,
            "MM/dd"
        )} (土)`;
    }
};

// --- Style/Display Logic ---
const getStatusColor = (status: "entered" | "not_entered") => {
    return status === "entered" ? "green" : "red";
};
</script>

<style scoped>
a {
    color: inherit;
}
.text-green {
    color: green;
}
.text-red {
    color: red;
}
td a {
    transition: background-color 0.2s ease-in-out;
}
td a:hover {
    background-color: #f5f5f5;
}
</style>
