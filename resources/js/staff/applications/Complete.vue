<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="shop" />
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title>申し込み完了</v-card-title>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import ShopHeader from "@/components/common/ShopHeader.vue";

const props = defineProps<{
    successMessage: string;
    shop: {
        slug: string;
        name: string;
    };
}>();
</script>
