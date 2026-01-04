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
                <!-- ダッシュボード -->
                <v-list-item :href="`/shops/${shop.slug}/staff/dashboard`" :active="currentPage === 'dashboard'"
                             title="ダッシュボード" prepend-icon="mdi-view-dashboard"></v-list-item>

                <v-divider class="my-2"></v-divider>

                <!-- 予約 -->
                <v-list-subheader>予約</v-list-subheader>
                <v-list-item :href="`/shops/${shop.slug}/staff/bookings`" :active="currentPage === 'bookings'"
                             title="予約一覧"></v-list-item>

                <v-divider class="my-2"></v-divider>

                <!-- 顧客 -->
                <v-list-subheader>顧客</v-list-subheader>
                <v-list-item :href="`/shops/${shop.slug}/staff/bookers`" :active="currentPage === 'bookers'"
                             title="予約者一覧"></v-list-item>

                <v-divider class="my-2"></v-divider>

                <!-- 業務 -->
                <v-list-subheader>業務</v-list-subheader>
                <v-list-item :href="`/shops/${shop.slug}/staff/shifts`" :active="currentPage === 'shifts'"
                             title="シフト確認"></v-list-item>
                <v-list-item :href="`/shops/${shop.slug}/staff/staffs`" :active="currentPage === 'staffs'"
                             title="スタッフ一覧"></v-list-item>

                <v-divider class="my-2"></v-divider>

                <!-- 設定 -->
                <v-list-subheader>設定</v-list-subheader>
                <v-list-item :href="`/shops/${shop.slug}/staff/profile`" :active="currentPage === 'profile'"
                             title="プロフィール"></v-list-item>
            </v-list>

            <template #append>
                <!-- スタッフ用は戻るボタン等は現状要件にないが、ログアウト等のスペースとして空けておく -->
                <v-divider></v-divider>
                <!-- 必要であればここに追加 -->
            </template>
        </v-navigation-drawer>

        <!-- Main Content -->
        <v-main>
            <slot></slot>
        </v-main>
    </v-app>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";

interface Shop {
    name: string;
    slug: string;
}

defineProps<{
    shop: Shop;
    currentPage?: string;
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
