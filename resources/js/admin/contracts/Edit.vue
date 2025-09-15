<template>
    <v-card class="mx-auto my-5" max-width="800">
        <v-card-title>契約情報編集 (ID: {{ contract.id }})</v-card-title>

        <v-card-text>
            <v-form :action="updateUrl" method="POST">
                <input type="hidden" name="_token" :value="csrfToken">
                <input type="hidden" name="_method" value="PUT">

                <v-text-field
                    label="オーナー"
                    :model-value="contract.user.owner.name"
                    disabled
                ></v-text-field>

                <v-text-field
                    label="最大店舗数"
                    name="max_shops"
                    type="number"
                    :model-value="oldInput.max_shops || contract.max_shops"
                    :error-messages="errors.max_shops"
                ></v-text-field>

                <v-text-field
                    label="契約開始日"
                    name="start_date"
                    type="date"
                    :model-value="oldInput.start_date || formattedStartDate"
                    :error-messages="errors.start_date"
                ></v-text-field>

                <v-text-field
                    label="契約終了日"
                    name="end_date"
                    type="date"
                    :model-value="oldInput.end_date || formattedEndDate"
                    :error-messages="errors.end_date"
                ></v-text-field>

                <v-select
                    label="ステータス"
                    :items="['active', 'inactive']"
                    name="status"
                    :model-value="oldInput.status || contract.status"
                    :error-messages="errors.status"
                ></v-select>

                <v-btn type="submit" color="primary" class="mt-4">更新する</v-btn>
            </v-form>
        </v-card-text>
    </v-card>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    contract: Object as () => any,
    updateUrl: String,
    csrfToken: String,
    errors: Object as () => any,
    oldInput: Object as () => any,
});

const formattedStartDate = computed(() => props.contract.start_date.split('T')[0]);
const formattedEndDate = computed(() => props.contract.end_date.split('T')[0]);

</script>
