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
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title>プロフィール編集</v-card-title>
                    <v-card-text>
                        <form
                              id="form"
                              :action="formActionUrl"
                              method="POST"
                              enctype="multipart/form-data">
                            <input
                                   type="hidden"
                                   name="_token"
                                   :value="props.csrfToken" />
                            <input type="hidden" name="_method" value="PUT" />

                            <v-text-field
                                          v-model="form.nickname"
                                          name="nickname"
                                          label="ニックネーム *"
                                          required
                                          :rules="[rules.required]"></v-text-field>

                            <ImageUploader
                                           label="プロフィール画像(小)"
                                           :initial-url="props.staff.profile?.small_image_url ?? null"
                                           :max-size="2"
                                           recommended-resolution="正方形 (推奨)"
                                           input-name="small_image" />

                            <ImageUploader
                                           label="プロフィール画像(大)"
                                           :initial-url="props.staff.profile?.large_image_url ?? null"
                                           :max-size="5"
                                           recommended-resolution="横長 (推奨)"
                                           input-name="large_image" />
                        </form>
                    </v-card-text>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn form="form" color="primary" type="submit" :disabled="!isFormValid">
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
import ShopHeader from "@/components/common/ShopHeader.vue";
import ImageUploader from "@/components/common/ImageUploader.vue";

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

onMounted(() => {
    if (props.oldInput) {
        form.value.nickname = props.oldInput.nickname ?? form.value.nickname;
    }
});

const rules = {
    required: (value: any) => !!(value || value === 0) || "必須項目です。",
};

const isFormValid = computed(() => {
    return rules.required(form.value.nickname) === true;
});
</script>
