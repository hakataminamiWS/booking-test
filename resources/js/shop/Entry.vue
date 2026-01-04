<template>
    <v-container class="container-width-600">
        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="shop" />
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12" class="text-center py-8">
                <h2 class="text-h5 font-weight-bold mb-2">ご予約へ進む</h2>
                <p v-if="shop.accepts_online_bookings" class="text-body-2 text-grey-darken-1 mb-8">
                    ログインして予約、または会員登録せずに予約を選択してください。
                </p>
                <p v-else class="text-body-2 text-error mb-8 font-weight-bold">
                    現在、オンラインからの予約受付を停止しています。<br>
                    店舗へ直接お問い合わせください。
                </p>

                <div v-if="shop.accepts_online_bookings" class="d-flex flex-column gap-4">
                    <v-btn
                           :href="`/shops/${shop.slug}/booker/bookings/create`"
                           color="primary"
                           size="x-large"
                           variant="elevated"
                           prepend-icon="mdi-login"
                           class="mb-4 w-100 py-6"
                           height="auto">
                        <div class="d-flex flex-column align-start py-1">
                            <div class="text-subtitle-1 font-weight-bold">ログインして予約する</div>
                            <div class="text-caption opacity-80">会員の方、会員登録して予約する方</div>
                        </div>
                    </v-btn>

                    <v-btn
                           :href="`/shops/${shop.slug}/guest/bookings/create`"
                           variant="outlined"
                           size="x-large"
                           prepend-icon="mdi-account-off"
                           class="w-100 py-6"
                           height="auto">
                        <div class="d-flex flex-column align-start py-1">
                            <div class="text-subtitle-1 font-weight-bold">ゲストとして予約する</div>
                            <div class="text-caption text-grey-darken-1">会員登録せずに予約する方</div>
                        </div>
                    </v-btn>
                </div>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import ShopHeader from "@/components/common/ShopHeader.vue";

interface Shop {
    name: string;
    slug: string;
    accepts_online_bookings: boolean;
}

defineProps<{
    shop: Shop;
}>();
</script>

<style scoped>
.container-width-600 {
    max-width: 600px;
}

.gap-4 {
    gap: 16px;
}
</style>
