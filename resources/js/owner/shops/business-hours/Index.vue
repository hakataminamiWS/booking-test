<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn
                       :href="shopShowUrl"
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
                    <v-card-title
                                  :class="{
                                    'd-flex': true,
                                    'flex-column': smAndDown,
                                    'align-start': smAndDown,
                                    'justify-space-between': !smAndDown,
                                    'align-center': !smAndDown,
                                }">営業時間一覧</v-card-title>
                    <v-card-text>
                        <!-- Section 1: Regular Business Hours -->
                        <div
                             class="d-flex justify-space-between align-center mt-4">
                            <h6 class="text-h6">通常営業時間・定休日</h6>
                            <v-btn
                                   :class="{ 'mt-2': smAndDown }"
                                   :href="regularHoursEditUrl"
                                   color="primary">編集する</v-btn>
                        </div>
                        <v-table class="mt-2">
                            <thead>
                                <tr>
                                    <th class="text-left">曜日</th>
                                    <th class="text-left">ステータス</th>
                                    <th class="text-left">営業時間</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="hour in props.businessHours"
                                    :key="hour.day_of_week">
                                    <td>{{ dayOfWeek[hour.day_of_week] }}</td>
                                    <td>
                                        <v-chip
                                                :color="hour.is_open ? 'blue' : 'grey'
                                                    "
                                                dark
                                                small>
                                            {{
                                                hour.is_open
                                                    ? "営業日"
                                                    : "定休日"
                                            }}
                                        </v-chip>
                                    </td>
                                    <td>
                                        {{
                                            hour.is_open
                                                ? `${hour.start_time} - ${hour.end_time}`
                                                : "-"
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>

                        <!-- Section 2: Special Open Days -->
                        <div
                             class="d-flex justify-space-between align-center mt-8">
                            <h6 class="text-h6">特別営業日</h6>
                            <v-btn
                                   :class="{ 'mt-2': smAndDown }"
                                   prepend-icon="mdi-plus"
                                   :href="specialOpenDaysCreateUrl"
                                   color="primary">新規登録する</v-btn>
                        </div>
                        <v-table class="mt-2">
                            <thead>
                                <tr>
                                    <th class="text-left">日付</th>
                                    <th class="text-left">営業時間</th>
                                    <th class="text-left">営業日名</th>
                                    <th class="text-left">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="props.specialOpenDays.length === 0">
                                    <td colspan="4" class="text-center">
                                        登録されている特別営業日はありません。
                                    </td>
                                </tr>
                                <tr
                                    v-for="day in props.specialOpenDays"
                                    :key="day.id">
                                    <td>
                                        {{ day.date }} ({{
                                            getDayOfWeekString(day.date)
                                        }})
                                    </td>
                                    <td>
                                        {{ day.start_time }} -
                                        {{ day.end_time }}
                                    </td>
                                    <td>{{ day.name }}</td>
                                    <td>
                                        <v-btn
                                               color="primary"
                                               :href="specialOpenDayEditUrl(day.id)">編集する</v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>

                        <!-- Section 3: Special Closed Days -->
                        <div
                             class="d-flex justify-space-between align-center mt-8">
                            <h6 class="text-h6">特別休業日</h6>
                            <v-btn
                                   :class="{ 'mt-2': smAndDown }"
                                   prepend-icon="mdi-plus"
                                   :href="specialClosedDaysCreateUrl"
                                   color="primary">新規登録する</v-btn>
                        </div>
                        <v-table class="mt-2">
                            <thead>
                                <tr>
                                    <th class="text-left">期間</th>
                                    <th class="text-left">休業日名</th>
                                    <th class="text-left">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="props.specialClosedDays.length === 0">
                                    <td colspan="3" class="text-center">
                                        登録されている特別休業日はありません。
                                    </td>
                                </tr>
                                <tr
                                    v-for="day in props.specialClosedDays"
                                    :key="day.id">
                                    <td>
                                        {{ day.start_at }} - {{ day.end_at }}
                                    </td>
                                    <td>{{ day.name }}</td>
                                    <td>
                                        <v-btn
                                               color="primary"
                                               :href="specialClosedDayEditUrl(day.id)">編集する</v-btn>
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
import ShopHeader from "@/owner/shops/components/ShopHeader.vue";

const props = defineProps({
    shop: Object,
    businessHours: Array,
    specialOpenDays: Array,
    specialClosedDays: Array,
});
const { smAndDown } = useDisplay();
const shopShowUrl = computed(() => `/owner/shops/${props.shop.slug}`);

const regularHoursEditUrl = computed(
    () => `/owner/shops/${props.shop.slug}/business-hours/regular/edit`
);

const specialOpenDaysCreateUrl = computed(
    () =>
        `/owner/shops/${props.shop.slug}/business-hours/special-open-days/create`
);

const specialOpenDayEditUrl = (id) =>
    `/owner/shops/${props.shop.slug}/business-hours/special-open-days/${id}/edit`;

const specialClosedDaysCreateUrl = computed(
    () =>
        `/owner/shops/${props.shop.slug}/business-hours/special-closed-days/create`
);

const specialClosedDayEditUrl = (id) =>
    `/owner/shops/${props.shop.slug}/business-hours/special-closed-days/${id}/edit`;

const dayOfWeek = computed(() => {
    return {
        0: "日曜",
        1: "月曜",
        2: "火曜",
        3: "水曜",
        4: "木曜",
        5: "金曜",
        6: "土曜",
    };
});

const getDayOfWeekString = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString("ja-JP", { weekday: "short" });
};
</script>
