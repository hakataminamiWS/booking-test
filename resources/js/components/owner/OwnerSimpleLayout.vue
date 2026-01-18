<template>
    <v-app>
        <v-app-bar color="primary">
            <v-app-bar-title>オーナー管理画面</v-app-bar-title>

            <v-spacer></v-spacer>

            <!-- ログアウト -->
            <form id="logout-form" action="/logout" method="POST" style="display: none;">
                <input type="hidden" name="_token" :value="csrfToken">
            </form>
            <v-btn variant="text" class="text-white" @click="logout">
                ログアウト
            </v-btn>
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
import { ref, onMounted } from "vue";
import FlashMessage from "@/components/common/FlashMessage.vue";

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
