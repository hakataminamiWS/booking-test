<template>
    <v-card class="mb-4">
        <v-card-title>{{ label }}</v-card-title>
        <v-card-text>
            <div class="d-flex flex-column align-center justify-center">
                <v-avatar size="128" class="mb-4" @mouseover="isHovering = true" @mouseleave="isHovering = false">
                    <v-img v-if="previewUrl" :src="previewUrl" cover></v-img>
                    <v-icon v-else size="96">mdi-account-circle</v-icon>
                    <v-overlay
                        v-model="isHovering"
                        contained
                        scrim="#000000"
                        class="align-center justify-center"
                        @click="fileInput?.click()"
                    >
                        <v-icon size="48">mdi-pencil</v-icon>
                    </v-overlay>
                </v-avatar>

                <v-btn @click="fileInput?.click()" class="mb-2">画像を変更</v-btn>
                <div class="text-caption text-medium-emphasis">
                    ファイル容量: {{ maxSize }}MBまで<br />
                    推奨サイズ: {{ recommendedResolution }}
                </div>

                <v-file-input
                    ref="fileInput"
                    :rules="fileRules"
                    accept="image/jpeg,image/png,image/webp"
                    label="画像を選択"
                    prepend-icon="mdi-camera"
                    hide-details
                    class="d-none"
                    @change="onFileChange($event)"
                ></v-file-input>

                <v-btn
                    v-if="previewUrl"
                    color="error"
                    variant="outlined"
                    class="mt-2"
                    @click="deleteImage"
                >
                    画像を削除
                </v-btn>
            </div>
            <v-alert
                v-if="errorMessage"
                type="error"
                class="mt-4"
                density="compact"
                >{{ errorMessage }}</v-alert
            >
        </v-card-text>
    </v-card>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    label: string;
    initialUrl: string | null;
    maxSize: number; // MB単位
    recommendedResolution: string;
}>();

const emit = defineEmits<{
    (event: 'update:file', file: File | null): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(props.initialUrl);
const errorMessage = ref<string | null>(null);
const isHovering = ref(false);

// ファイルバリデーションルール
const fileRules = [
    (value: File) => {
        if (!value) return true;

        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(value.type)) {
            return '許可されていないファイル形式です (jpeg, png, webpのみ)。';
        }
        if (value.size > props.maxSize * 1024 * 1024) {
            return `画像サイズは${props.maxSize}MBまでです。`;
        }
        return true;
    },
];

// ファイル選択時の処理
const onFileChange = (event: Event) => {
    errorMessage.value = null;
    const inputElement = event.target as HTMLInputElement;
    const selectedFile = inputElement.files?.[0];

    if (!selectedFile) {
        return;
    }

    // クライアントサイドバリデーション
    const validationResult = fileRules[0](selectedFile);
    if (typeof validationResult === 'string') {
        errorMessage.value = validationResult;
        if (fileInput.value) {
            fileInput.value.value = '';
        }
        return;
    }

    // プレビューURLを生成
    previewUrl.value = URL.createObjectURL(selectedFile);
    // 親コンポーネントにFileオブジェクトを渡す
    emit('update:file', selectedFile);
};

// 画像削除処理
const deleteImage = () => {
    errorMessage.value = null;
    previewUrl.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    // 親コンポーネントにnullを渡す
    emit('update:file', null);
};

// initialUrlの変更を監視してpreviewUrlを更新
watch(() => props.initialUrl, (newUrl) => {
    previewUrl.value = newUrl;
});
</script>
