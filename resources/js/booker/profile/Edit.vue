<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-btn
                       href="/booker/shops"
                       prepend-icon="mdi-arrow-left"
                       variant="text">
                    登録店舗一覧へ戻る
                </v-btn>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="shop" />
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title>プロフィール編集</v-card-title>
                    <v-card-text>
                        <v-alert
                                 v-if="props.successMessage"
                                 type="success"
                                 class="mb-4">
                            {{ props.successMessage }}
                        </v-alert>

                        <v-alert
                                 v-if="props.errors.length > 0"
                                 type="error"
                                 class="mb-4">
                            <ul>
                                <li v-for="error in props.errors" :key="error">{{ error }}</li>
                            </ul>
                        </v-alert>

                        <form
                              id="form"
                              :action="formActionUrl"
                              method="POST">
                            <input
                                   type="hidden"
                                   name="_token"
                                   :value="props.csrfToken" />
                            <input type="hidden" name="_method" value="PUT" />

                            <v-text-field
                                          :model-value="props.booker.number"
                                          label="会員番号"
                                          readonly
                                          variant="filled"></v-text-field>

                            <v-text-field
                                          v-model="form.name"
                                          name="name"
                                          label="お名前 *"
                                          required
                                          :rules="[rules.required]"></v-text-field>

                            <v-textarea
                                          v-model="form.note_from_booker"
                                          name="note_from_booker"
                                          label="店舗へのメモ"
                                          rows="3"></v-textarea>

                            <v-text-field
                                          v-model="form.contact_email"
                                          name="contact_email"
                                          label="連絡先メールアドレス *"
                                          type="email"
                                          :rules="[rules.required]"></v-text-field>

                            <v-text-field
                                          v-model="form.contact_phone"
                                          name="contact_phone"
                                          label="連絡先電話番号 *"
                                          :rules="[rules.required]"></v-text-field>

                            <p class="text-caption text-grey">※メールアドレスと電話番号の両方が必須です。</p>
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

interface Shop {
    name: string;
    slug: string;
}

interface ShopBooker {
    id: number;
    number: number;
    name: string;
    contact_email: string | null;
    contact_phone: string | null;
    note_from_booker: string | null;
}

const props = defineProps<{
    shop: Shop;
    booker: ShopBooker;
    errors: string[];
    oldInput: { [key: string]: any } | null;
    csrfToken: string;
    successMessage: string | null;
}>();

const formActionUrl = computed(
    () => `/shops/${props.shop.slug}/booker/profile`
);

const form = ref({
    name: props.booker.name ?? "",
    contact_email: props.booker.contact_email ?? "",
    contact_phone: props.booker.contact_phone ?? "",
    note_from_booker: props.booker.note_from_booker ?? "",
});

onMounted(() => {
    if (props.oldInput) {
        form.value.name = props.oldInput.name ?? form.value.name;
        form.value.contact_email = props.oldInput.contact_email ?? form.value.contact_email;
        form.value.contact_phone = props.oldInput.contact_phone ?? form.value.contact_phone;
        form.value.note_from_booker = props.oldInput.note_from_booker ?? form.value.note_from_booker;
    }
});

const rules = {
    required: (value: any) => !!(value || value === 0) || "必須項目です。",
};

const isFormValid = computed(() => {
    return (
        rules.required(form.value.name) === true &&
        rules.required(form.value.contact_email) === true &&
        rules.required(form.value.contact_phone) === true
    );
});
</script>
