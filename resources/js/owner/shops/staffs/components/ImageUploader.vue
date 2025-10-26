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
                    <v-progress-circular
                        v-if="loading"
                        indeterminate
                        color="primary"
                        size="64"
                        width="6"
                        class="overlay-spinner"
                    ></v-progress-circular>
                </v-avatar>

                <v-btn @click="fileInput?.click()" class="mb-2">画像を変更</v-btn>
                <div class="text-caption text-medium-emphasis">
                    ファイル容量: {{ maxSize }}MBまで<br />
                    推奨サイズ: {{ recommendedResolution }}
                </div>

                <v-file-input
                    ref="fileInput"
                    v-model="file"
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
                    :loading="loading"
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
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps<{ 
    label: string;
    initialPath: string | null;
    shopSlug: string;
    staffId: number;
    csrfToken: string;
    maxSize: number; // MB単位
    recommendedResolution: string;
    imageType: 'small' | 'large'; // 追加
}>();

const emit = defineEmits<{(event: 'update:path', path: string | null): void;}>();

const fileInput = ref<HTMLInputElement | null>(null);
const file = ref<File[]>([]);
const previewUrl = ref<string | null>(props.initialPath);
const loading = ref(false);
const errorMessage = ref<string | null>(null);
const isHovering = ref(false);

// ファイルバリデーションルール
const fileRules = [
    (value: File[]) => {
        if (!value || value.length === 0) return true;
        const file = value[0];
        if (!file) return true; // Add this check

        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            return '許可されていないファイル形式です (jpeg, png, webpのみ)。';
        }
        if (file.size > props.maxSize * 1024 * 1024) {
            return `画像サイズは${props.maxSize}MBまでです。`;
        }
        return true;
    },
];

// ファイル選択時の処理
const onFileChange = async (event: Event) => {
    errorMessage.value = null;
    const inputElement = event.target as HTMLInputElement;
    const selectedFile = inputElement.files?.[0];

    if (!selectedFile) {
        file.value = []; // ファイル選択をリセット
        return;
    }

    // クライアントサイドバリデーション
    for (const rule of fileRules) {
        const result = rule([selectedFile]); // selectedFileを配列として渡す
        if (typeof result === 'string') {
            errorMessage.value = result;
            file.value = []; // ファイル選択をリセット
            return;
        }
    }

    loading.value = true;
    const formData = new FormData();
    formData.append('image', selectedFile);

    const uploadUrl = `/owner/api/shops/${props.shopSlug}/staffs/${props.staffId}/${props.imageType}-image`;

    try {
        const response = await axios.post(
            uploadUrl,
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        );
        previewUrl.value = response.data.url;
        emit('update:path', response.data.path);
    } catch (error: any) {
        console.error('Full error response:', error.response.data); // Add this for debugging
        if (error.response && error.response.status === 422) {
            errorMessage.value = error.response.data.errors.image ? error.response.data.errors.image[0] : 'ファイルのアップロードに失敗しました。';
        } else {
            errorMessage.value = 'ファイルのアップロード中にエラーが発生しました。';
            console.error('Upload error:', error);
        }
        previewUrl.value = props.initialPath; // エラー時は元の画像に戻す
    } finally {
        loading.value = false;
        file.value = []; // ファイル選択をリセット
    }
};

// 画像削除処理
const deleteImage = async () => {
    errorMessage.value = null;
    loading.value = true;
    const deleteUrl = `/owner/api/shops/${props.shopSlug}/staffs/${props.staffId}/${props.imageType}-image`;
    try {
        await axios.delete(
            deleteUrl,
            {
                headers: {
                },
            }
        );
        previewUrl.value = null;
        emit('update:path', null);
    } catch (error: any) {
        errorMessage.value = '画像の削除中にエラーが発生しました。';
        console.error('Delete error:', error);
    } finally {
        loading.value = false;
    }
};

// initialPathの変更を監視してpreviewUrlを更新
watch(() => props.initialPath, (newPath) => {
    previewUrl.value = newPath;
});

</script>

<style scoped>
.overlay-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
</style>
