<template>
    <v-app>
        <!-- Mobile App Bar -->
        <v-app-bar
                   v-if="isMobile"
                   color="primary"
                   density="compact">
            <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
            <v-app-bar-title>{{ shop.name }}</v-app-bar-title>
        </v-app-bar>

        <!-- Navigation Drawer -->
        <v-navigation-drawer
                             v-model="drawer"
                             :permanent="!isMobile"
                             :temporary="isMobile"
                             width="220">
            <!-- Shop Name Header (PC only) -->
            <v-list-item
                         v-if="!isMobile"
                         :title="shop.name"
                         class="py-4 bg-primary">
                <template #prepend>
                    <v-icon color="white">mdi-store</v-icon>
                </template>
                <template #title>
                    <span class="text-white font-weight-bold">{{ shop.name }}</span>
                </template>
            </v-list-item>

            <v-divider></v-divider>

            <!-- Menu Items -->
            <v-list density="compact" nav>
                <!-- Home -->
                <v-list-item :href="`/owner/shops/${shop.slug}/dashboard`" :active="currentPage === 'dashboard'"
                             title="ダッシュボード"></v-list-item>

                <v-divider class="my-2"></v-divider>

                <!-- 予約 -->
                <v-list-subheader>予約</v-list-subheader>
                <v-list-item :href="`/owner/shops/${shop.slug}/bookings`" :active="currentPage === 'bookings'"
                             title="予約一覧"></v-list-item>

                <v-divider class="my-2"></v-divider>

                <!-- 顧客 -->
                <v-list-subheader>顧客</v-list-subheader>
                <v-list-item :href="`/owner/shops/${shop.slug}/bookers`" :active="currentPage === 'bookers'"
                             title="予約者一覧"></v-list-item>

                <v-divider class="my-2"></v-divider>

                <!-- スタッフ -->
                <v-list-subheader>スタッフ</v-list-subheader>
                <v-list-item :href="`/owner/shops/${shop.slug}/staffs`" :active="currentPage === 'staffs'"
                             title="スタッフ一覧"></v-list-item>
                <v-list-item :href="`/owner/shops/${shop.slug}/shifts`" :active="currentPage === 'shifts'"
                             title="シフト管理"></v-list-item>
                <v-list-item :href="`/owner/shops/${shop.slug}/staff-applications`"
                             :active="currentPage === 'staff-applications'"
                             title="スタッフ申請"></v-list-item>

                <v-divider class="my-2"></v-divider>

                <!-- メニュー -->
                <v-list-subheader>メニュー</v-list-subheader>
                <v-list-item :href="`/owner/shops/${shop.slug}/menus`" :active="currentPage === 'menus'"
                             title="メニュー一覧"></v-list-item>
                <v-list-item :href="`/owner/shops/${shop.slug}/options`" :active="currentPage === 'options'"
                             title="オプション一覧"></v-list-item>

                <v-divider class="my-2"></v-divider>

                <!-- 設定 -->
                <v-list-subheader>設定</v-list-subheader>
                <v-list-item :href="`/owner/shops/${shop.slug}`" :active="currentPage === 'home'"
                             title="店舗情報"></v-list-item>
                <v-list-item :href="`/owner/shops/${shop.slug}/business-hours`"
                             :active="currentPage === 'business-hours'"
                             title="営業時間"></v-list-item>
            </v-list>

            <template #append>
                <v-divider></v-divider>
                <v-list density="compact" nav>
                    <v-list-item
                                 href="/owner/shops"
                                 title="店舗一覧へ戻る"></v-list-item>
                </v-list>
            </template>
        </v-navigation-drawer>

        <!-- Main Content -->
        <v-main>
            <v-container class="py-2">
                <FlashMessage />
            </v-container>
            <slot></slot>
        </v-main>
    </v-app>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import FlashMessage from "@/components/common/FlashMessage.vue";

interface Shop {
    name: string;
    slug: string;
}

defineProps<{
    shop: Shop;
    currentPage?: string;
    // flash props are provided globally now
}>();

const drawer = ref(true);
const windowWidth = ref(window.innerWidth);

const isMobile = computed(() => windowWidth.value < 768);

const handleResize = () => {
    windowWidth.value = window.innerWidth;
};

onMounted(() => {
    window.addEventListener("resize", handleResize);
    // モバイル時は初期状態でドロワーを閉じる
    if (isMobile.value) {
        drawer.value = false;
    }
});

onUnmounted(() => {
    window.removeEventListener("resize", handleResize);
});
</script>

<style scoped>
/* PC時のメインコンテンツの左マージンを調整 */
@media (min-width: 768px) {
    :deep(.v-main) {
        padding-left: 220px !important;
    }
}
</style>
