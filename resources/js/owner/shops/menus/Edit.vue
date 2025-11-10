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
                <ShopHeader :shop="props.shop" />

                <v-card class="mt-4">
                    <v-card-title>メニュー編集</v-card-title>
                    <v-card-text>
                        <v-alert
                            v-if="props.errors.length > 0"
                            type="error"
                            class="mb-4"
                            closable
                        >
                            <ul>
                                <li
                                    v-for="(error, index) in props.errors"
                                    :key="index"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                        </v-alert>

                        <form :action="formAction" method="POST">
                            <input
                                type="hidden"
                                name="_token"
                                :value="csrfToken"
                            />
                            <input type="hidden" name="_method" value="PUT" />

                            <v-text-field
                                v-model="formData.name"
                                name="name"
                                label="メニュー名"
                                required
                                hint="お客様に表示されるメニューの正式名称を入力します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-text-field
                                v-model="formData.price"
                                name="price"
                                label="価格"
                                type="number"
                                suffix="円"
                                required
                                hint="メニューの価格を円単位で入力します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-text-field
                                v-model="formData.duration"
                                name="duration"
                                label="所要時間"
                                type="number"
                                suffix="分"
                                required
                                hint="サービスの所要時間を分単位で入力します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-textarea
                                v-model="formData.description"
                                name="description"
                                label="メニューの説明"
                                hint="お客様に表示されるメニューの詳細な説明を入力します。（任意）"
                                persistent-hint
                                class="mb-4"
                            ></v-textarea>

                            <v-switch
                                v-model="formData.requires_staff_assignment"
                                :true-value="1"
                                :false-value="0"
                                label="担当者の割り当て"
                                hint="このメニューの予約時に、担当スタッフの選択を必須にするかどうかを設定します。"
                                persistent-hint
                                inset
                            ></v-switch>
                            <input
                                type="hidden"
                                name="requires_staff_assignment"
                                :value="
                                    formData.requires_staff_assignment ? 1 : 0
                                "
                            />

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
                                class="mb-4"
                            ></v-select>

                            <template
                                v-for="staffId in formData.staff_ids"
                                :key="staffId"
                            >
                                <input
                                    type="hidden"
                                    name="staff_ids[]"
                                    :value="staffId"
                                />
                            </template>

                            <v-switch
                                v-model="
                                    formData.requires_cancellation_deadline
                                "
                                :true-value="1"
                                :false-value="0"
                                label="特別なキャンセル期限"
                                hint="店舗の基本設定とは異なるキャンセル期限を設定する場合にオンにします。"
                                persistent-hint
                                inset
                            ></v-switch>
                            <input
                                type="hidden"
                                name="requires_cancellation_deadline"
                                :value="
                                    formData.requires_cancellation_deadline
                                        ? 1
                                        : 0
                                "
                            />

                            <v-text-field
                                v-if="formData.requires_cancellation_deadline"
                                v-model="formData.cancellation_deadline_minutes"
                                name="cancellation_deadline_minutes"
                                label="キャンセル期限"
                                type="number"
                                suffix="分前"
                                hint="予約の何分前までお客様によるキャンセルを許可するか設定します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-switch
                                v-model="formData.requires_booking_deadline"
                                :true-value="1"
                                :false-value="0"
                                label="特別な予約締切"
                                hint="店舗の基本設定とは異なる予約締切を設定する場合にオンにします。"
                                persistent-hint
                                inset
                            ></v-switch>
                            <input
                                type="hidden"
                                name="requires_booking_deadline"
                                :value="
                                    formData.requires_booking_deadline ? 1 : 0
                                "
                            />

                            <v-text-field
                                v-if="formData.requires_booking_deadline"
                                v-model="formData.booking_deadline_minutes"
                                name="booking_deadline_minutes"
                                label="予約締切"
                                type="number"
                                suffix="分前"
                                hint="予約の何分前でオンライン予約の受付を締め切るか設定します。"
                                persistent-hint
                                class="mb-4"
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

        <v-dialog v-model="deleteDialog" max-width="500px">
            <v-card>
                <v-card-title class="text-h5"
                    >本当に削除しますか？</v-card-title
                >
                <v-card-text>この操作は元に戻せません。</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="blue-darken-1"
                        variant="text"
                        @click="deleteDialog = false"
                        >キャンセル</v-btn
                    >
                    <form
                        :action="deleteFormAction"
                        method="POST"
                        style="display: inline"
                    >
                        <input type="hidden" name="_token" :value="csrfToken" />
                        <input type="hidden" name="_method" value="DELETE" />
                        <v-btn color="error" variant="text" type="submit"
                            >削除する</v-btn
                        >
                    </form>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import ShopHeader from "@/owner/shops/components/ShopHeader.vue";

const deleteDialog = ref(false);

const props = defineProps({
    shop: { type: Object, required: true },
    menu: { type: Object, required: true },
    staffs: { type: Array, required: true },
    csrfToken: { type: String, required: true },
    errors: { type: Array, default: () => [] },
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
    staff_ids: props.menu.staffs.map((staff) => staff.id),
});
</script>
