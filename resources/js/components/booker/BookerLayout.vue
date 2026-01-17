<template>
    <v-app>
        <!-- Mobile App Bar -->
        <v-app-bar color="primary" density="compact">
            <v-container class="d-flex align-center pa-0 header-container">
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
            </v-container>
        </v-app-bar>

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
</script>

<style scoped>
.header-container {
    max-width: 600px;
}
</style>
