<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn
                    :href="`/owner/shops/${props.shop.slug}`"
                    prepend-icon="mdi-arrow-left"
                >
                    店舗詳細に戻る
                </v-btn>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="props.shop" />

                <v-card>
                    <v-card-title>店舗情報編集</v-card-title>
                    <v-divider></v-divider>
                    <v-card-text>
                        <form
                            :action="`/owner/shops/${props.shop.slug}`"
                            method="POST"
                        >
                            <input
                                type="hidden"
                                name="_token"
                                :value="props.csrfToken"
                            />
                            <input type="hidden" name="_method" value="PUT" />

                            <v-alert
                                v-if="props.errors.length > 0"
                                type="error"
                                class="mb-4"
                            >
                                <ul>
                                    <li
                                        v-for="(error, i) in props.errors"
                                        :key="i"
                                    >
                                        {{ error }}
                                    </li>
                                </ul>
                            </v-alert>

                            <v-text-field
                                v-model="form.name"
                                name="name"
                                label="店舗名 *"
                                required
                            ></v-text-field>

                            <v-text-field
                                v-model="form.slug"
                                name="slug"
                                label="店舗 ID"
                                readonly
                                disabled
                            ></v-text-field>

                            <v-select
                                v-model="form.time_slot_interval"
                                name="time_slot_interval"
                                :items="[15, 30, 60]"
                                label="予約枠の間隔（分） *"
                                required
                            ></v-select>

                            <v-radio-group
                                v-model="form.booking_confirmation_type"
                                name="booking_confirmation_type"
                                required
                            >
                                <template v-slot:label
                                    ><div>予約承認方法 *</div></template
                                >
                                <v-radio
                                    label="自動承認"
                                    value="automatic"
                                ></v-radio>
                                <v-radio
                                    label="手動承認"
                                    value="manual"
                                ></v-radio>
                            </v-radio-group>

                            <v-radio-group
                                v-model="form.accepts_online_bookings"
                                name="accepts_online_bookings"
                                required
                            >
                                <template v-slot:label
                                    ><div>オンライン予約受付 *</div></template
                                >
                                <v-radio
                                    label="受け付ける"
                                    :value="1"
                                ></v-radio>
                                <v-radio
                                    label="受け付けない"
                                    :value="0"
                                ></v-radio>
                            </v-radio-group>

                            <v-text-field
                                v-model="form.timezone"
                                label="タイムゾーン"
                                readonly
                                disabled
                            ></v-text-field>

                            <v-text-field
                                v-model.number="
                                    form.cancellation_deadline_minutes
                                "
                                name="cancellation_deadline_minutes"
                                label="キャンセル期限（分前） *"
                                type="number"
                                required
                            ></v-text-field>

                            <v-text-field
                                v-model.number="form.booking_deadline_minutes"
                                name="booking_deadline_minutes"
                                label="予約締切（分前） *"
                                type="number"
                                required
                            ></v-text-field>

                            <v-card-actions>
                                <v-btn
                                    color="error"
                                    @click="deleteDialog = true"
                                    >削除する</v-btn
                                >
                                <v-spacer></v-spacer>
                                <v-btn type="submit" color="primary"
                                    >更新する</v-btn
                                >
                            </v-card-actions>
                        </form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Delete Dialog -->
        <v-dialog v-model="deleteDialog" max-width="600px">
            <v-card>
                <v-card-title class="text-h5"
                    >店舗を削除しますか？</v-card-title
                >
                <v-card-text>
                    <p>
                        この操作は元に戻せません。店舗を削除するには、以下の店舗名を入力してください。
                    </p>
                    <p class="font-weight-bold text-center my-4">
                        {{ props.shop.name }}
                    </p>
                    <v-text-field
                        v-model="confirmationText"
                        label="店舗名を入力"
                        outlined
                    ></v-text-field>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="deleteDialog = false">キャンセル</v-btn>
                    <form :action="deleteUrl" method="POST">
                        <input
                            type="hidden"
                            name="_token"
                            :value="props.csrfToken"
                        />
                        <input type="hidden" name="_method" value="DELETE" />
                        <v-btn
                            color="error"
                            type="submit"
                            :disabled="!isDeleteConfirmed"
                        >
                            削除を実行
                        </v-btn>
                    </form>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import ShopHeader from "./components/ShopHeader.vue";

interface Shop {
    name: string;
    slug: string;
    time_slot_interval: number;
    booking_confirmation_type: string;
    accepts_online_bookings: boolean;
    timezone: string;
    cancellation_deadline_minutes: number;
    booking_deadline_minutes: number;
}

const props = defineProps<{
    shop: Shop;
    errors: string[];
    oldInput: { [key: string]: any } | null;
    csrfToken: string;
}>();

const form = ref({
    name: "",
    slug: "",
    time_slot_interval: 30,
    booking_confirmation_type: "automatic",
    accepts_online_bookings: 1,
    timezone: "Asia/Tokyo",
    cancellation_deadline_minutes: 1440,
    booking_deadline_minutes: 0,
});

onMounted(() => {
    const source =
        props.oldInput && Object.keys(props.oldInput).length > 0
            ? props.oldInput
            : props.shop;

    form.value.name = source.name ?? "";
    form.value.slug = props.shop.slug; // slug is readonly
    form.value.time_slot_interval = source.time_slot_interval ?? 30;
    form.value.booking_confirmation_type =
        source.booking_confirmation_type ?? "automatic";
    form.value.accepts_online_bookings = source.hasOwnProperty(
        "accepts_online_bookings"
    )
        ? Number(source.accepts_online_bookings)
        : 1;
    form.value.timezone = props.shop.timezone; // timezone is readonly
    form.value.cancellation_deadline_minutes =
        source.cancellation_deadline_minutes ?? 1440;
    form.value.booking_deadline_minutes = source.booking_deadline_minutes ?? 0;
});

// --- URLs ---
const deleteUrl = computed(() => `/owner/shops/${props.shop.slug}`);

// --- Delete Dialog ---
const deleteDialog = ref(false);
const confirmationText = ref("");
const isDeleteConfirmed = computed(
    () => confirmationText.value === props.shop.name
);
</script>
