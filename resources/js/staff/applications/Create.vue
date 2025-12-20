<template>
    <v-container>
        <v-row>
            <v-col cols="12">
                <ShopHeader :shop="shop" />
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card>
                    <v-card-title>スタッフ登録申し込み</v-card-title>
                    <v-card-subtitle>「{{ props.shop.name }}」へのスタッフ登録を申し込みます。</v-card-subtitle>

                    <v-card-text>
                        <form :action="`/shops/${props.shop.slug}/staff/apply`" method="POST">
                            <input type="hidden" name="_token" :value="props.csrfToken">

                            <v-alert v-if="props.errors.length > 0" type="error" class="mb-4">
                                <ul>
                                    <li v-for="(error, i) in props.errors" :key="i">{{ error }}</li>
                                </ul>
                            </v-alert>

                            <v-text-field
                                          v-model="form.name"
                                          name="name"
                                          label="表示名 *"
                                          required></v-text-field>

                            <v-btn type="submit" color="primary">
                                申し込みを送信する
                            </v-btn>
                        </form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import ShopHeader from "@/components/common/ShopHeader.vue";

const props = defineProps<{
    shop: {
        slug: string;
        name: string;
    };
    errors: string[];
    oldInput: { [key: string]: any } | null;
    csrfToken: string;
}>();

const form = ref({
    name: '',
});

onMounted(() => {
    if (props.oldInput) {
        form.value.name = props.oldInput.name ?? '';
    }
});
</script>
