<template>
    <v-app>
        <v-app-bar color="primary">
            <v-app-bar-title>オーナー管理画面</v-app-bar-title>
        </v-app-bar>

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
</script>
