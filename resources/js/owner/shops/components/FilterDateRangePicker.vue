
<template>
    <div class="d-flex align-center">
        <!-- Start Date -->
        <v-menu v-model="menuFrom" :close-on-content-click="false" location="bottom start">
            <template v-slot:activator="{ props }">
                <v-text-field
                    :model-value="value"
                    label="開始日"
                    readonly
                    v-bind="props"
                    density="compact"
                    hide-details
                    class="mr-2"
                    append-inner-icon="mdi-calendar"
                    clearable
                    @click:clear="$emit('update:value', null)"
                ></v-text-field>
            </template>
            <v-date-picker
                v-model="dateFrom"
                @update:model-value="onFromChange"
                hide-header
                color="primary"
            ></v-date-picker>
        </v-menu>

        <span class="mx-2">〜</span>

        <!-- End Date -->
        <v-menu v-model="menuTo" :close-on-content-click="false" location="bottom start">
            <template v-slot:activator="{ props }">
                <v-text-field
                    :model-value="valueTo"
                    label="終了日"
                    readonly
                    v-bind="props"
                    density="compact"
                    hide-details
                    append-inner-icon="mdi-calendar"
                    clearable
                    @click:clear="$emit('update:valueTo', null)"
                ></v-text-field>
            </template>
            <v-date-picker
                v-model="dateTo"
                @update:model-value="onToChange"
                hide-header
                color="primary"
            ></v-date-picker>
        </v-menu>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { format, parseISO } from 'date-fns';

const props = defineProps<{
    value?: string | null;
    valueTo?: string | null;
}>();

const emit = defineEmits(['update:value', 'update:valueTo']);

const menuFrom = ref(false);
const menuTo = ref(false);

const dateFrom = ref<Date | undefined>(props.value ? parseISO(props.value) : undefined);
const dateTo = ref<Date | undefined>(props.valueTo ? parseISO(props.valueTo) : undefined);

watch(() => props.value, (newVal) => {
    dateFrom.value = newVal ? parseISO(newVal) : undefined;
});

watch(() => props.valueTo, (newVal) => {
    dateTo.value = newVal ? parseISO(newVal) : undefined;
});

const onFromChange = (date: any) => {
    if (date) {
        emit('update:value', format(date, 'yyyy-MM-dd'));
        menuFrom.value = false;
    }
};

const onToChange = (date: any) => {
    if (date) {
        emit('update:valueTo', format(date, 'yyyy-MM-dd'));
        menuTo.value = false;
    }
};
</script>
