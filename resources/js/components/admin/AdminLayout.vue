<template>
    <v-app>
        <!-- モバイル用アプリバー -->
        <v-app-bar
                   v-if="isMobile"
                   color="blue-grey-darken-3"
                   density="compact">
            <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
            <v-app-bar-title>System Admin</v-app-bar-title>
        </v-app-bar>

        <!-- ナビゲーションドロワー -->
        <!-- color プロパティでドロワーの背景色を設定 -->
        <v-navigation-drawer
                             v-model="drawer"
                             :permanent="!isMobile"
                             :temporary="isMobile"
                             color="blue-grey-darken-3"
                             width="240">

            <!-- ヘッダー (PCのみ) -->
            <v-list-item
                         v-if="!isMobile"
                         class="py-4">
                <template #title>
                    <span class="text-white font-weight-bold">
                        予約システム管理者
                    </span>
                </template>
            </v-list-item>

            <v-divider></v-divider>

            <!-- メニュー項目 -->
            <v-list density="compact" nav>
                <!-- 契約管理 -->
                <v-list-subheader class="text-blue-grey-lighten-1 font-weight-bold">
                    契約管理
                </v-list-subheader>

                <v-list-item href="/admin/contract-applications" :active="currentPage === 'contract-applications'"
                             title="契約申込一覧">
                </v-list-item>

                <v-list-item href="/admin/contracts" :active="currentPage === 'contracts'" title="契約一覧"></v-list-item>

            </v-list>

            <template #append>
                <v-divider></v-divider>
                <v-list density="compact" nav>
                    <!-- ログアウトフォーム (非表示) -->
                    <form id="logout-form" action="/logout" method="POST" style="display: none;">
                        <input type="hidden" name="_token" :value="csrfToken">
                    </form>

                    <v-list-item
                                 title="ログアウト"
                                 @click="logout"></v-list-item>
                </v-list>
            </template>
        </v-navigation-drawer>

        <!-- メインコンテンツ -->
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

interface Props {
    currentPage?: string;
}

defineProps<Props>();

const drawer = ref(true);
const windowWidth = ref(window.innerWidth);
const csrfToken = ref('');

const isMobile = computed(() => windowWidth.value < 768);

const handleResize = () => {
    windowWidth.value = window.innerWidth;
};

const logout = () => {
    const form = document.getElementById('logout-form') as HTMLFormElement;
    if (form) {
        form.submit();
    }
};

onMounted(() => {
    window.addEventListener("resize", handleResize);
    if (isMobile.value) {
        drawer.value = false;
    }
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (tokenMeta) {
        csrfToken.value = tokenMeta.getAttribute('content') || '';
    }
});

onUnmounted(() => {
    window.removeEventListener("resize", handleResize);
});
</script>

<style scoped>
@media (min-width: 768px) {
    :deep(.v-main) {
        padding-left: 240px !important;
    }
}
</style>
