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

                <v-card class="mt-4">
                    <v-card-title>プロフィール編集</v-card-title>
                    <v-card-text>
                        <form :action="formActionUrl" method="POST">
                            <input
                                type="hidden"
                                name="_token"
                                :value="props.csrfToken"
                            />
                            <input type="hidden" name="_method" value="PUT" />

                            <v-alert
                                v-if="props.errors.length > 0"
                                type="error"
                                class="mb-4"
                            >
                                <ul>
                                    <li
                                        v-for="(error, i) in props.errors"
                                        :key="i"
                                    >
                                        {{ error }}
                                    </li>
                                </ul>
                            </v-alert>

                            <v-text-field
                                v-model="form.nickname"
                                name="nickname"
                                label="ニックネーム *"
                                required
                            ></v-text-field>

                            <ImageUploader
                                label="プロフィール画像(小)"
                                :initial-path="props.staff.profile?.small_image_url ?? null"
                                :shop-slug="props.shop.slug"
                                :staff-id="props.staff.id"
                                :csrf-token="props.csrfToken"
                                :max-size="1"
                                recommended-resolution="300px × 300px"
                                image-type="small"
                                @update:path="form.small_image_path = $event"
                            />
                            <input type="hidden" name="small_image_path" :value="form.small_image_path" />

                            <ImageUploader
                                label="プロフィール画像(大)"
                                :initial-path="props.staff.profile?.large_image_url ?? null"
                                :shop-slug="props.shop.slug"
                                :staff-id="props.staff.id"
                                :csrf-token="props.csrfToken"
                                :max-size="3"
                                recommended-resolution="800px × 800px"
                                image-type="large"
                                @update:path="form.large_image_path = $event"
                            />
                            <input
                                type="hidden"
                                name="large_image_path"
                                :value="form.large_image_path"
                            />

                            <v-btn color="primary" type="submit" class="mt-4">
                                更新する
                            </v-btn>
                        </form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import ShopHeader from "@/owner/shops/components/ShopHeader.vue";
import ImageUploader from "@/owner/shops/staffs/components/ImageUploader.vue";

interface Shop {
    name: string;
    slug: string;
}

interface ShopStaffProfile {
    nickname: string;
    small_image_path: string | null;
    large_image_path: string | null;
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
}>();

const staffsIndexUrl = computed(() => `/owner/shops/${props.shop.slug}/staffs`);
const formActionUrl = computed(() => `/owner/shops/${props.shop.slug}/staffs/${props.staff.id}`);

const form = ref({
    nickname: props.staff.profile?.nickname ?? '',
    small_image_path: props.staff.profile?.small_image_path ?? '',
    large_image_path: props.staff.profile?.large_image_path ?? '',
});

onMounted(() => {
    if (props.oldInput) {
        form.value.nickname = props.oldInput.nickname ?? form.value.nickname;
        form.value.small_image_path = props.oldInput.small_image_path ?? form.value.small_image_path;
        form.value.large_image_path = props.oldInput.large_image_path ?? form.value.large_image_path;
    }
});
</script>
