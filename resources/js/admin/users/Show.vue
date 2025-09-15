<template>
    <v-card v-if="user" class="mx-auto my-5" max-width="600">
        <v-card-title>ユーザー詳細</v-card-title>
        <v-list>
            <v-list-item>
                <v-list-item-title>ID</v-list-item-title>
                <v-list-item-subtitle>{{ user.id }}</v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
                <v-list-item-title>公開ID</v-list-item-title>
                <v-list-item-subtitle>{{ user.public_id }}</v-list-item-subtitle>
            </v-list-item>
            <v-list-item>
                <v-list-item-title>ゲスト</v-list-item-title>
                <v-list-item-subtitle>{{ user.is_guest ? 'はい' : 'いいえ' }}</v-list-item-subtitle>
            </v-list-item>
        </v-list>

        <v-divider></v-divider>

        <v-card-title>役割管理</v-card-title>
        <div class="pa-4">
            <div v-if="isOwner">
                <div class="d-flex align-center">
                    <span class="mr-2">現在の役割:</span>
                    <v-chip color="success" variant="elevated">オーナー</v-chip>
                </div>

                <div v-if="!hasContract" class="mt-4">
                    <v-alert type="info" variant="tonal" class="mb-4">
                        このオーナーにはまだ契約がありません。
                    </v-alert>
                    <v-btn :href="createContractUrl" color="primary">契約を新規作成</v-btn>
                </div>
                <div v-else class="mt-4">
                     <v-alert type="success" variant="tonal">
                        契約済みです。
                    </v-alert>
                </div>

                <hr class="my-4">

                <form :action="removeActionUrl" method="POST">
                    <input type="hidden" name="_token" :value="csrfToken">
                    <input type="hidden" name="_method" value="DELETE">
                    <v-btn type="submit" color="error">オーナーから外す</v-btn>
                </form>
            </div>
            <div v-else>
                <p>現在の役割: オーナーではありません</p>
                <form :action="addActionUrl" method="POST" class="mt-4">
                    <input type="hidden" name="_token" :value="csrfToken">
                    <v-btn type="submit" color="primary">オーナーに設定</v-btn>
                </form>
            </div>
        </div>
    </v-card>
</template>

<script setup lang="ts">
defineProps({
    user: Object,
    isOwner: Boolean,
    hasContract: Boolean,
    addActionUrl: String,
    removeActionUrl: String,
    createContractUrl: String,
    csrfToken: String,
});
</script>
