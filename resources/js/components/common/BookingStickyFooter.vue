<template>
    <v-footer app fixed bottom elevation="4" border class="bg-surface">
        <v-container class="py-2 mx-auto" :style="{ maxWidth: maxWidth }" :class="{ 'px-0': mobile }">
            <v-row align="center" no-gutters>
                <!-- デスクトップ表示（モバイルでは非表示） -->
                <v-col v-if="!shouldUseMobileLayout" class="d-flex align-center flex-wrap gap-4">

                    <!-- Prependスロット（削除ボタン等） -->
                    <div v-if="$slots.prepend" class="mr-4">
                        <slot name="prepend"></slot>
                    </div>

                    <div class="d-flex align-center">
                        <span class="text-caption text-medium-emphasis mr-2">日時:</span>
                        <span class="text-body-2 font-weight-bold">
                            {{ dateTime || '未選択' }}
                        </span>
                    </div>

                    <v-divider vertical class="mx-4"></v-divider>

                    <div class="d-flex align-center">
                        <span class="text-caption text-medium-emphasis mr-2">メニュー:</span>
                        <span class="text-body-2 font-weight-bold text-truncate" style="max-width: 200px;">
                            {{ menuName || '未選択' }}
                        </span>
                    </div>

                    <v-divider vertical class="mx-4"></v-divider>

                    <div class="d-flex align-center">
                        <span class="text-caption text-medium-emphasis mr-2">担当スタッフ:</span>
                        <span class="text-body-2 font-weight-bold">
                            {{ staffName || '未選択' }}
                        </span>
                    </div>


                </v-col>

                <!-- モバイル表示（左側、縦積み） -->
                <v-col v-else class="d-flex align-center" style="flex: 1; min-width: 0;">
                    <!-- モバイル用Prependスロット（削除ボタン等） -->
                    <div v-if="$slots.prepend" class="mr-2">
                        <slot name="prepend"></slot>
                    </div>

                    <div class="d-flex flex-column text-caption lh-1">
                        <div class="d-flex align-center mb-1">
                            <span class="text-truncate font-weight-medium">{{ dateTime || '未選択' }}</span>
                        </div>
                        <div class="d-flex align-center mb-1">
                            <span class="text-medium-emphasis mr-1">メニュー:</span>
                            <span class="text-truncate font-weight-medium">{{ menuName || '未選択' }}</span>
                        </div>
                        <div class="d-flex align-center">
                            <span class="text-medium-emphasis mr-1">スタッフ:</span>
                            <span class="text-truncate font-weight-medium">{{ staffName || '未選択' }}</span>
                        </div>
                    </div>
                </v-col>

                <!-- アクション & 価格（右側） -->
                <v-col cols="auto" class="d-flex ml-auto"
                       :class="shouldUseMobileLayout ? 'flex-column align-end justify-center' : 'align-center'">
                    <!-- 価格 -->
                    <div :class="shouldUseMobileLayout ? 'mb-1' : 'mr-4 mb-0'" class="text-right">
                        <span class="text-caption text-medium-emphasis" v-if="!shouldUseMobileLayout">合計金額</span>
                        <span :class="shouldUseMobileLayout ? 'text-subtitle-1' : 'text-h6'"
                              class="font-weight-bold text-primary">
                            {{ totalPrice?.toLocaleString() }}円
                        </span>
                    </div>

                    <!-- 送信ボタン -->
                    <v-btn color="primary" :size="shouldUseMobileLayout ? 'default' : 'large'" :disabled="disabled"
                           @click="$emit('submit')"
                           :min-width="shouldUseMobileLayout ? 100 : 160" :height="shouldUseMobileLayout ? 36 : 44">
                        {{ submitLabel }}
                    </v-btn>
                </v-col>
            </v-row>
        </v-container>
    </v-footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useDisplay } from 'vuetify';

const props = withDefaults(defineProps<{
    menuName?: string;
    staffName?: string;
    dateTime?: string;
    totalPrice?: number;
    submitLabel?: string;
    disabled?: boolean;
    maxWidth?: string;
    mobileLayout?: boolean;
}>(), {
    maxWidth: '1200px',
    mobileLayout: false
});

defineEmits<{
    (e: 'submit'): void;
}>();

const { mobile } = useDisplay();

const shouldUseMobileLayout = computed(() => mobile.value || props.mobileLayout);
</script>

<style scoped></style>
