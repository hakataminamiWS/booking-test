<template>
    <v-app>
        <!-- Mobile App Bar -->
        <v-app-bar color="primary" density="compact">
            <v-container fluid class="d-flex align-center header-container">
                <!-- Back Link -->
                <v-btn
                       v-if="backLink"
                       :href="backLink"
                       variant="text"
                       class="text-white">
                    <v-icon start>mdi-chevron-left</v-icon>
                    {{ backLabel }}
                </v-btn>
                <v-app-bar-title>{{ shop.name }}</v-app-bar-title>

                <v-spacer></v-spacer>

                <!-- ログアウト -->
                <form id="logout-form" action="/logout" method="POST" style="display: none;">
                    <input type="hidden" name="_token" :value="csrfToken">
                </form>
                <v-btn variant="text" class="text-white" @click="logout">
                    ログアウト
                </v-btn>
            </v-container>
        </v-app-bar>

        <!-- Main Content -->
        <v-main>
            <v-container fluid class="py-2">
                <FlashMessage />
            </v-container>
            <slot></slot>
        </v-main>
    </v-app>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import FlashMessage from "@/components/common/FlashMessage.vue";

interface Shop {
    name: string;
    slug: string;
}

defineProps<{
    shop: Shop;
    backLink?: string;
    backLabel?: string;
}>();

const csrfToken = ref('');

const logout = () => {
    const form = document.getElementById('logout-form') as HTMLFormElement;
    if (form) {
        form.submit();
    }
};

onMounted(() => {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (tokenMeta) {
        csrfToken.value = tokenMeta.getAttribute('content') || '';
    }
});
</script>

<style scoped>
/* header-container style removed for fluid layout */
</style>
