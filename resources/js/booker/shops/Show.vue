<template>
    <v-container class="container-width-600">
        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="shop" />
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card class="mb-4">
                    <v-card-text>
                        <div class="d-flex justify-space-around text-center">
                            <div>
                                <div class="text-caption text-grey">最終予約日</div>
                                <div class="text-h6 font-weight-bold">
                                    {{ formatDate(booker.crm?.last_booking_at) }}
                                </div>
                            </div>
                            <div>
                                <div class="text-caption text-grey">来店回数</div>
                                <div class="text-h6 font-weight-bold">
                                    {{ booker.crm?.booking_count ?? 0 }}回
                                </div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card title="メニュー" class="mb-4">
                    <v-list>
                        <v-list-item
                                     title="新規予約"
                                     subtitle="新しい予約を行います"
                                     :href="`/shops/${shop.slug}/booker/bookings/create`"
                                     prepend-icon="mdi-calendar-plus"
                                     link
                                     class="py-3"></v-list-item>
                        <v-divider></v-divider>
                        <v-list-item
                                     title="予約履歴"
                                     subtitle="過去の予約や今後の予定を確認します"
                                     :href="`/shops/${shop.slug}/booker/bookings`"
                                     prepend-icon="mdi-history"
                                     link
                                     class="py-3"></v-list-item>
                        <v-divider></v-divider>
                        <v-list-item
                                     title="プロフィール編集"
                                     subtitle="登録情報の確認・変更を行います"
                                     :href="`/shops/${shop.slug}/booker/profile/edit`"
                                     prepend-icon="mdi-account-edit"
                                     link
                                     class="py-3"></v-list-item>
                    </v-list>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                               variant="text"
                               color="error"
                               prepend-icon="mdi-account-remove"
                               @click="withdrawDialog = true">
                            退会する
                        </v-btn>
                    </v-card-actions>
                    <v-spacer></v-spacer>
                </v-card>
            </v-col>
        </v-row>

        <v-dialog v-model="withdrawDialog" max-width="400">
            <v-card>
                <v-card-title class="bg-error text-white">
                    退会確認
                </v-card-title>
                <v-card-text class="pt-4">
                    <v-alert type="warning" variant="tonal" class="mb-4">
                        退会すると、この店舗での会員情報が削除されます。
                    </v-alert>
                    <p class="text-body-2 mb-2">
                        退会を確定するには、以下の会員番号を入力してください:
                    </p>
                    <p class="text-h6 font-weight-bold text-center mb-4">
                        {{ booker.number }}
                    </p>
                    <v-text-field
                                  v-model="memberNumberInput"
                                  label="会員番号"
                                  placeholder="会員番号を入力"
                                  :error-messages="withdrawError ? [withdrawError] : []"
                                  variant="outlined"
                                  density="compact"></v-text-field>
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-btn variant="text" @click="withdrawDialog = false">
                        キャンセル
                    </v-btn>
                    <v-spacer></v-spacer>
                    <v-btn
                           color="error"
                           variant="elevated"
                           :disabled="memberNumberInput !== booker.number"
                           @click="confirmWithdraw">
                        退会する
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <form
              ref="withdrawForm"
              :action="`/shops/${shop.slug}/booker/profile`"
              method="POST"
              style="display: none;">
            <input type="hidden" name="_token" :value="csrfToken" />
            <input type="hidden" name="_method" value="DELETE" />
            <input type="hidden" name="member_number" :value="memberNumberInput" />
        </form>
    </v-container>
</template>

<script setup lang="ts">
import { ref } from "vue";
import ShopHeader from "@/components/common/ShopHeader.vue";

interface Shop {
    name: string;
    slug: string;
}

interface ShopBooker {
    name: string;
    number: string;
    crm?: {
        last_booking_at: string | null;
        booking_count: number;
    };
}

const props = defineProps<{
    shop: Shop;
    booker: ShopBooker;
}>();

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const withdrawDialog = ref(false);
const memberNumberInput = ref('');
const withdrawError = ref('');
const withdrawForm = ref<HTMLFormElement | null>(null);

const formatDate = (dateString: string | null | undefined) => {
    if (!dateString) return "-";
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return "-";
    return `${d.getFullYear()}/${d.getMonth() + 1}/${d.getDate()}`;
};

const confirmWithdraw = () => {
    if (memberNumberInput.value !== props.booker.number) {
        withdrawError.value = '会員番号が一致しません。';
        return;
    }
    withdrawError.value = '';
    withdrawForm.value?.submit();
};
</script>

<style scoped>
.container-width-600 {
    max-width: 600px;
}
</style>
