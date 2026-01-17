<template>
    <div v-if="flash.success || flash.error || flash.status" class="mb-4">
        <v-alert v-if="flash.success" type="success" closable class="mb-2" variant="tonal">
            {{ flash.success }}
        </v-alert>
        <v-alert v-if="flash.error" type="error" closable class="mb-2" variant="tonal">
            {{ flash.error }}
        </v-alert>
        <v-alert v-if="flash.status" type="info" closable class="mb-2" variant="tonal">
            {{ flash.status }}
        </v-alert>
    </div>
</template>

<script setup lang="ts">
import { inject } from 'vue';

interface FlashMessages {
    success: string | null;
    error: string | null;
    status: string | null;
}

const flash = inject<FlashMessages>('flash', {
    success: null,
    error: null,
    status: null
});
</script>
