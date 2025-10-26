<template>
    <v-card variant="tonal">
        <v-card-text>
            <div class="text-subtitle-1">
                店舗：<span class="font-weight-bold">{{ shop.name }}</span>
                (店舗 ID: {{ shop.slug }})
            </div>
        </v-card-text>
    </v-card>
</template>

<script setup lang="ts">
interface Shop {
    name: string;
    slug: string;
}

const props = defineProps<{
    shop: Shop;
}>();
</script>
