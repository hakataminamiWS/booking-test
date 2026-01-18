<template>
    <OwnerLayout :shop="props.shop" currentPage="home">
        <v-container>
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
                                    }">
                            <span>店舗詳細</span>
                            <v-btn
                                   color="primary"
                                   :class="{ 'mt-2': smAndDown }"
                                   :href="`/owner/shops/${props.shop.slug}/edit`">
                                店舗詳細を編集する
                            </v-btn>
                        </v-card-title>
                        <v-divider></v-divider>
                        <v-card-text>
                            <v-table density="compact">
                                <tbody>
                                    <tr>
                                        <td>店舗名</td>
                                        <td>{{ props.shop.name }}</td>
                                    </tr>
                                    <tr>
                                        <td>店舗ID</td>
                                        <td>{{ props.shop.slug }}</td>
                                    </tr>
                                    <tr>
                                        <td>予約枠の間隔</td>
                                        <td>
                                            {{ props.shop.time_slot_interval }} 分
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>オンライン予約受付</td>
                                        <td>
                                            <v-chip
                                                    :color="props.shop
                                                        .accepts_online_bookings
                                                        ? 'green'
                                                        : 'red'
                                                        "
                                                    size="small">
                                                {{
                                                    props.shop
                                                        .accepts_online_bookings
                                                        ? "受付中"
                                                        : "停止中"
                                                }}
                                            </v-chip>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>タイムゾーン</td>
                                        <td>{{ props.shop.timezone }}</td>
                                    </tr>
                                    <tr>
                                        <td>キャンセル期限</td>
                                        <td>
                                            {{
                                                formatDeadline(
                                                    props.shop
                                                        .cancellation_deadline_minutes
                                                )
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>予約締切</td>
                                        <td>
                                            {{
                                                formatDeadline(
                                                    props.shop.booking_deadline_minutes
                                                )
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>登録日時</td>
                                        <td>
                                            {{
                                                new Date(
                                                    props.shop.created_at
                                                ).toLocaleString()
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>更新日時</td>
                                        <td>
                                            {{
                                                new Date(
                                                    props.shop.updated_at
                                                ).toLocaleString()
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </v-table>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </OwnerLayout>
</template>

<script setup lang="ts">
import { useDisplay } from "vuetify";
import OwnerLayout from "@/components/owner/OwnerLayout.vue";
import { useTimeFormatter } from "@/composables/useTimeFormatter";

interface Shop {
    id: number;
    name: string;
    slug: string;
    time_slot_interval: number;
    booking_confirmation_type: string;
    accepts_online_bookings: boolean;
    timezone: string;
    cancellation_deadline_minutes: number;
    booking_deadline_minutes: number;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    shop: Shop;
}>();

const { smAndDown } = useDisplay();
const { formatDeadline } = useTimeFormatter();

</script>
