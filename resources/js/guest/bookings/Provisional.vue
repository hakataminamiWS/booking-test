<template>
    <BookerLayout :shop="shop">
        <v-container class="container-width-600 px-4">
            <v-row justify="center">
                <v-col cols="12">
                    <v-card class="mb-6" variant="text">
                        <v-card-text class="text-center py-6">
                            <v-icon size="64" color="warning" class="mb-4">mdi-email-check-outline</v-icon>
                            <h2 class="text-h5 font-weight-bold mb-4">仮予約を受け付けました</h2>
                            
                            <p class="text-body-1 text-high-emphasis mb-6">
                                ご登録のメールアドレスに確認メールをお送りしました。<br>
                                メール内のリンクをクリックして、予約を確定してください。
                            </p>

                            <v-alert
                                type="info"
                                variant="tonal"
                                class="text-left mb-4"
                                icon="mdi-information"
                            >
                                <div class="text-body-2">
                                    <strong>メールが届かない場合</strong><br>
                                    迷惑メールフォルダやゴミ箱に自動的に振り分けられている可能性があります。<br>
                                    ご確認をお願いいたします。
                                </div>
                            </v-alert>

                            <p class="text-caption text-grey">
                                ※確認メールのリンク有効期限は10分間です。
                            </p>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </BookerLayout>
</template>

<script setup lang="ts">
import BookerLayout from "@/components/booker/BookerLayout.vue";

interface Shop {
    name: string;
    slug: string;
}

interface Booking {
    id: number;
}

interface Props {
    shop: Shop;
    booking: Booking;
}

defineProps<Props>();
</script>

<style scoped>
.container-width-600 {
    max-width: 600px;
}
</style>
