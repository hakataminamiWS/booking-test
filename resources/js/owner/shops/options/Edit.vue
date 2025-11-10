<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn :href="optionsIndexUrl" prepend-icon="mdi-arrow-left">
                    オプション一覧に戻る
                </v-btn>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="props.shop" />

                <v-card class="mt-4">
                    <v-card-title>オプション編集</v-card-title>
                    <v-card-text>
                        <v-alert
                            v-if="props.errors.length > 0"
                            type="error"
                            class="mb-4"
                            closable
                        >
                            <ul>
                                <li
                                    v-for="(error, index) in props.errors"
                                    :key="index"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                        </v-alert>

                        <form :action="formAction" method="POST">
                            <input
                                type="hidden"
                                name="_token"
                                :value="csrfToken"
                            />
                            <input type="hidden" name="_method" value="PUT" />

                            <v-text-field
                                v-model="formData.name"
                                name="name"
                                label="オプション名"
                                required
                                hint="お客様に表示されるオプションの正式名称を入力します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-text-field
                                v-model="formData.price"
                                name="price"
                                label="追加料金"
                                type="number"
                                suffix="円"
                                required
                                hint="オプションの価格を円単位で入力します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-text-field
                                v-model="formData.additional_duration"
                                name="additional_duration"
                                label="追加所要時間"
                                type="number"
                                suffix="分"
                                required
                                hint="サービスの所要時間に追加される時間を分単位で入力します。"
                                persistent-hint
                                class="mb-4"
                            ></v-text-field>

                            <v-textarea
                                v-model="formData.description"
                                name="description"
                                label="オプションの説明"
                                hint="お客様に表示されるオプションの詳細な説明を入力します。（任意）"
                                persistent-hint
                                class="mb-4"
                            ></v-textarea>

                            <v-card-actions>
                                <v-btn
                                    color="error"
                                    @click="deleteDialog = true"
                                    >削除する</v-btn
                                >
                                <v-spacer></v-spacer>
                                <v-btn type="submit" color="primary"
                                    >更新する</v-btn
                                >
                            </v-card-actions>
                        </form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <v-dialog v-model="deleteDialog" max-width="500px">
            <v-card>
                <v-card-title class="text-h5"
                    >本当に削除しますか？</v-card-title
                >
                <v-card-text>この操作は元に戻せません。</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="blue-darken-1"
                        variant="text"
                        @click="deleteDialog = false"
                        >キャンセル</v-btn
                    >
                    <form
                        :action="deleteFormAction"
                        method="POST"
                        style="display: inline"
                    >
                        <input type="hidden" name="_token" :value="csrfToken" />
                        <input type="hidden" name="_method" value="DELETE" />
                        <v-btn color="error" variant="text" type="submit"
                            >削除する</v-btn
                        >
                    </form>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import ShopHeader from "@/owner/shops/components/ShopHeader.vue";

const deleteDialog = ref(false);

const props = defineProps({
    shop: { type: Object, required: true },
    option: { type: Object, required: true },
    csrfToken: { type: String, required: true },
    errors: { type: Array, default: () => [] },
});

const optionsIndexUrl = computed(() => `/owner/shops/${props.shop.slug}/options`);
const formAction = computed(
    () => `/owner/shops/${props.shop.slug}/options/${props.option.id}`
);
const deleteFormAction = computed(
    () => `/owner/shops/${props.shop.slug}/options/${props.option.id}`
);

const formData = ref({
    name: props.option.name,
    price: props.option.price,
    additional_duration: props.option.additional_duration,
    description: props.option.description,
});
</script>
