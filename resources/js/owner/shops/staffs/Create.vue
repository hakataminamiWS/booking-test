<template>
    <OwnerLayout :shop="props.shop" currentPage="staffs">
        <v-container>

            <v-row>
                <v-col cols="12">
                    <v-card>
                        <v-card-title>予約枠用スタッフ登録</v-card-title>
                        <v-card-text>
                            <form id="form" :action="formActionUrl" method="POST">
                                <input
                                       type="hidden"
                                       name="_token"
                                       :value="props.csrfToken" />

                                <v-text-field
                                              v-model="form.nickname"
                                              name="nickname"
                                              label="ニックネーム *"
                                              required
                                              :error-messages="nicknameErrors"></v-text-field>
                            </form>
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn form="form" color="primary" type="submit">
                                登録する
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </OwnerLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import OwnerLayout from "@/components/owner/OwnerLayout.vue";

interface Shop {
    name: string;
    slug: string;
}

const props = defineProps<{
    shop: Shop;
    errors: string[];
    oldInput: { [key: string]: any } | null;
    csrfToken: string;
}>();

const staffsIndexUrl = computed(() => `/owner/shops/${props.shop.slug}/staffs`);
const formActionUrl = computed(() => `/owner/shops/${props.shop.slug}/staffs`);

const form = ref({
    nickname: "",
});

const nicknameErrors = computed(() => {
    // Laravelのバリデーションエラーメッセージからnicknameに関するものを抽出
    return props.errors.filter((error) =>
        error.toLowerCase().includes("nickname")
    );
});

onMounted(() => {
    if (props.oldInput) {
        form.value.nickname = props.oldInput.nickname ?? "";
    }
});
</script>
