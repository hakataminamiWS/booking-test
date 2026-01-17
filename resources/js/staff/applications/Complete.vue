<template>
    <StaffSimpleLayout :shop="shop">
        <v-card>
            <v-card-title>申し込み完了</v-card-title>
            <v-card-text>
                申し込みを受け付けました。オーナーの承認をお待ちください。
            </v-card-text>
        </v-card>
    </StaffSimpleLayout>
</template>

<script setup lang="ts">
import StaffSimpleLayout from "@/components/staff/StaffSimpleLayout.vue";

const props = defineProps<{

    shop: {
        slug: string;
        name: string;
    };
}>();
</script>
