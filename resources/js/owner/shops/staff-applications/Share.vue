<template>
    <OwnerLayout :shop="props.shop" currentPage="staff-applications">
        <v-container>
            <!-- 上部リンク -->
            <v-row>
                <v-col cols="12">
                    <v-btn
                           :href="`/owner/shops/${props.shop.slug}/staff-applications`"
                           prepend-icon="mdi-arrow-left">
                        スタッフ申請一覧へ戻る
                    </v-btn>
                </v-col>
            </v-row>

            <!-- メインコンテンツ -->
            <v-row>
                <v-col cols="12">
                    <v-card>
                        <v-card-title>スタッフ申請リンク</v-card-title>
                        <v-divider></v-divider>
                        <v-card-text>
                            <v-alert type="info" variant="tonal" class="mb-4">
                                このリンクをスタッフ登録予定のユーザーに送ってください。<br>
                                スタッフ登録の際、Google または LINE でのサインインが必要です。
                            </v-alert>

                            <v-text-field
                                          v-model="staffApplicationUrl"
                                          label="スタッフ申請URL"
                                          readonly
                                          variant="outlined"
                                          class="mt-4">
                                <template #append-inner>
                                    <v-btn
                                           variant="tonal"
                                           size="small"
                                           @click="copyToClipboard">
                                        <v-icon start>mdi-content-copy</v-icon>
                                        コピー
                                    </v-btn>
                                </template>
                            </v-text-field>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- コピー成功スナックバー -->
            <v-snackbar v-model="snackbar" :timeout="2000" color="success">
                URLをコピーしました
            </v-snackbar>
        </v-container>
    </OwnerLayout>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import OwnerLayout from "@/components/owner/OwnerLayout.vue";

interface Shop {
    name: string;
    slug: string;
}

const props = defineProps<{
    shop: Shop;
}>();

const snackbar = ref(false);

const staffApplicationUrl = computed(() => {
    return `${window.location.origin}/shops/${props.shop.slug}/staff/apply`;
});

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(staffApplicationUrl.value);
        snackbar.value = true;
    } catch (err) {
        console.error("Failed to copy:", err);
    }
};
</script>
