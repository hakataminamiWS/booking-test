<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn :href="staffsIndexUrl" prepend-icon="mdi-arrow-left">
                    スタッフ一覧に戻る
                </v-btn>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="props.shop" />

                <v-card>
                    <v-card-title>プロフィール編集</v-card-title>
                    <v-card-text>
                        <form
                            id="form"
                            :action="formActionUrl"
                            method="POST"
                            enctype="multipart/form-data"
                        >
                            <input
                                type="hidden"
                                name="_token"
                                :value="props.csrfToken"
                            />
                            <input type="hidden" name="_method" value="PUT" />

                            <v-text-field
                                v-model="form.nickname"
                                name="nickname"
                                label="ニックネーム *"
                                required
                            ></v-text-field>

                            <v-label>プロフィール画像(小)</v-label>
                            <v-img
                                :src="
                                    smallImagePreview
                                        ? smallImagePreview
                                        : undefined
                                "
                                max-width="48"
                                class="mb-2"
                            >
                                <template v-slot:placeholder>
                                    <div
                                        class="d-flex align-center justify-center fill-height"
                                    >
                                        <v-icon size="48"
                                            >mdi-account-circle</v-icon
                                        >
                                    </div>
                                </template>
                            </v-img>
                            <v-file-input
                                name="small_image"
                                label="画像を変更"
                                accept="image/jpeg,image/png,image/webp"
                                prepend-icon="mdi-camera"
                                class="mb-4"
                                @update:model-value="
                                    (files) => updatePreview(files, 'small')
                                "
                            ></v-file-input>

                            <v-label>プロフィール画像(大)</v-label>
                            <v-img
                                :src="
                                    largeImagePreview
                                        ? largeImagePreview
                                        : undefined
                                "
                                max-width="192"
                                class="mb-2"
                            >
                                <template v-slot:placeholder>
                                    <div
                                        class="d-flex align-center justify-center fill-height"
                                    >
                                        <v-icon size="96"
                                            >mdi-account-circle</v-icon
                                        >
                                    </div>
                                </template>
                            </v-img>
                            <v-file-input
                                name="large_image"
                                label="画像を変更"
                                accept="image/jpeg,image/png,image/webp"
                                prepend-icon="mdi-camera"
                                class="mb-4"
                                @update:model-value="
                                    (files) => updatePreview(files, 'large')
                                "
                            ></v-file-input>
                        </form>
                    </v-card-text>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn form="form" color="primary" type="submit">
                            更新する
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import ShopHeader from "@/owner/shops/components/ShopHeader.vue";

interface Shop {
    name: string;
    slug: string;
}

interface ShopStaffProfile {
    nickname: string;
    small_image_url: string | null;
    large_image_url: string | null;
}

interface ShopStaff {
    id: number;
    profile: ShopStaffProfile;
}

const props = defineProps<{
    shop: Shop;
    staff: ShopStaff;
    errors: string[];
    oldInput: { [key: string]: any } | null;
    csrfToken: string;
    successMessage: string | null;
}>();

const staffsIndexUrl = computed(() => `/owner/shops/${props.shop.slug}/staffs`);
const formActionUrl = computed(
    () => `/owner/shops/${props.shop.slug}/staffs/${props.staff.id}`
);

const form = ref({
    nickname: props.staff.profile?.nickname ?? "",
});

const smallImagePreview = ref<string | null>(
    props.staff.profile?.small_image_url ?? null
);
const largeImagePreview = ref<string | null>(
    props.staff.profile?.large_image_url ?? null
);

const updatePreview = (files: File | File[], type: "small" | "large") => {
    const file = Array.isArray(files) ? files[0] : files;

    if (file) {
        const previewUrl = URL.createObjectURL(file);
        if (type === "small") {
            smallImagePreview.value = previewUrl;
        } else {
            largeImagePreview.value = previewUrl;
        }
    } else {
        // ファイルがクリアされた場合、元の画像に戻す
        if (type === "small") {
            smallImagePreview.value =
                props.staff.profile?.small_image_url ?? null;
        } else {
            largeImagePreview.value =
                props.staff.profile?.large_image_url ?? null;
        }
    }
};

onMounted(() => {
    if (props.oldInput) {
        form.value.nickname = props.oldInput.nickname ?? form.value.nickname;
    }
});
</script>
