<template>
    <v-card class="mx-auto my-5" max-width="800">
        <v-card-title>新規契約作成</v-card-title>

        <v-card-text>
            <v-form :action="storeUrl" method="POST">
                <input type="hidden" name="_token" :value="csrfToken">

                <v-select
                    label="オーナー"
                    :items="owners"
                    item-title="name"
                    item-value="user.id"
                    name="user_id"
                    v-model="selectedOwner"
                    :error-messages="errors.user_id"
                ></v-select>

                <v-text-field
                    label="最大店舗数"
                    name="max_shops"
                    type="number"
                    :model-value="oldInput.max_shops || 1"
                    :error-messages="errors.max_shops"
                ></v-text-field>

                <v-text-field
                    label="契約開始日"
                    name="start_date"
                    type="date"
                    :model-value="oldInput.start_date"
                    :error-messages="errors.start_date"
                ></v-text-field>

                <v-text-field
                    label="契約終了日"
                    name="end_date"
                    type="date"
                    :model-value="oldInput.end_date"
                    :error-messages="errors.end_date"
                ></v-text-field>

                <v-select
                    label="ステータス"
                    :items="['active', 'inactive']"
                    name="status"
                    :model-value="oldInput.status || 'active'"
                    :error-messages="errors.status"
                ></v-select>

                <v-btn type="submit" color="primary" class="mt-4">契約を作成</v-btn>
            </v-form>
        </v-card-text>
    </v-card>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps({
    owners: Array as () => any[],
    selectedUserId: Number,
    storeUrl: String,
    csrfToken: String,
    errors: Object as () => any,
    oldInput: Object as () => any,
});

const selectedOwner = ref(props.oldInput?.user_id || props.selectedUserId || null);

</script>
