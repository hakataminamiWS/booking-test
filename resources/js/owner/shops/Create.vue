<template>
    <v-card>
        <v-card-title>店舗の新規登録</v-card-title>
        <v-card-text>
            <form action="/owner/shops" method="POST">
                <input type="hidden" name="_token" :value="props.csrfToken">

                <v-alert v-if="props.errors.length > 0" type="error" class="mb-4">
                    <ul>
                        <li v-for="(error, i) in props.errors" :key="i">{{ error }}</li>
                    </ul>
                </v-alert>

                <v-text-field
                    v-model="form.name"
                    name="name"
                    label="店舗名 *"
                    required
                ></v-text-field>

                <v-text-field
                    v-model="form.slug"
                    name="slug"
                    label="店舗 ID *"
                    :error-messages="slugErrorMessages"
                    :success-messages="slugSuccessMessages"
                    @blur="validateSlug"
                    required
                    hint="予約ページのURLに使われる半角英数字とハイフンのみの文字列です。例: hakata-minami-store"
                    persistent-hint
                ></v-text-field>

                <v-select
                    v-model="form.time_slot_interval"
                    name="time_slot_interval"
                    :items="[15, 30, 60]"
                    label="予約枠の間隔（分） *"
                    required
                ></v-select>

                <v-radio-group v-model="form.booking_confirmation_type" name="booking_confirmation_type" required>
                    <template v-slot:label><div>予約承認方法 *</div></template>
                    <v-radio label="自動承認" value="automatic"></v-radio>
                    <v-radio label="手動承認" value="manual"></v-radio>
                </v-radio-group>

                <v-radio-group v-model="form.accepts_online_bookings" name="accepts_online_bookings" required>
                    <template v-slot:label><div>オンライン予約受付 *</div></template>
                    <v-radio label="受け付ける" :value="1"></v-radio>
                    <v-radio label="受け付けない" :value="0"></v-radio>
                </v-radio-group>

                <v-text-field
                    v-model="form.timezone"
                    name="timezone"
                    label="タイムゾーン"
                    readonly
                ></v-text-field>

                <v-text-field
                    v-model.number="form.cancellation_deadline_minutes"
                    name="cancellation_deadline_minutes"
                    label="キャンセル期限（分前） *"
                    type="number"
                    required
                    hint="予約の何分前までお客様によるキャンセルを許可するか設定します。(例: 1440 分 = 24 時間前)"
                    persistent-hint
                ></v-text-field>

                <v-text-field
                    v-model.number="form.booking_deadline_minutes"
                    name="booking_deadline_minutes"
                    label="予約締切（分前） *"
                    type="number"
                    required
                    hint="予約の何分前でオンライン予約の受付を締め切るか設定します。(0 は直前まで許可)"
                    persistent-hint
                ></v-text-field>

                <v-btn type="submit" color="primary" :disabled="isSubmitting || !isSlugChecked || !isSlugValid">登録する</v-btn>
            </form>
        </v-card-text>
    </v-card>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps<{
    errors: string[];
    oldInput: { [key: string]: any } | null;
    csrfToken: string;
}>();

const form = ref({
    name: '',
    slug: '',
    time_slot_interval: 30,
    booking_confirmation_type: 'automatic',
    accepts_online_bookings: 1,
    timezone: 'Asia/Tokyo',
    cancellation_deadline_minutes: 1440,
    booking_deadline_minutes: 0,
});

const slugErrorMessages = ref<string[]>([]);
const slugSuccessMessages = ref<string[]>([]);
const isSlugValid = ref(false);
const isSlugChecked = ref(false);
const isSubmitting = ref(false);

onMounted(() => {
    if (props.oldInput) {
        form.value.name = props.oldInput.name ?? '';
        form.value.slug = props.oldInput.slug ?? '';
        form.value.time_slot_interval = props.oldInput.time_slot_interval ?? 30;
        form.value.booking_confirmation_type = props.oldInput.booking_confirmation_type ?? 'automatic';
        form.value.accepts_online_bookings = props.oldInput.hasOwnProperty('accepts_online_bookings') ? Number(props.oldInput.accepts_online_bookings) : 1;
        form.value.cancellation_deadline_minutes = props.oldInput.cancellation_deadline_minutes ?? 1440;
        form.value.booking_deadline_minutes = props.oldInput.booking_deadline_minutes ?? 0;
        if(form.value.slug) {
            validateSlug();
        }
    }
});

const validateSlug = async () => {
    slugErrorMessages.value = [];
    slugSuccessMessages.value = [];
    isSlugValid.value = false;
    isSlugChecked.value = false;

    if (!form.value.slug) {
        slugErrorMessages.value = ['店舗 ID は必須です。'];
        return;
    }

    if (!/^[a-z0-9-]+$/.test(form.value.slug)) {
        slugErrorMessages.value = ['店舗 ID は半角英数字とハイフンのみ使用できます。'];
        return;
    }

    const reservedWords = ['create', 'edit'];
    if (reservedWords.includes(form.value.slug)) {
        slugErrorMessages.value = ['このIDは予約されているため使用できません。'];
        isSlugValid.value = false;
        isSlugChecked.value = true;
        return;
    }

    try {
        const response = await axios.get(`/owner/api/shops/validate-slug?slug=${form.value.slug}`);
        if (response.data.is_valid) {
            slugSuccessMessages.value = ['このIDは使用できます。'];
            isSlugValid.value = true;
        } else {
            slugErrorMessages.value = [response.data.message];
        }
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            slugErrorMessages.value = [error.response.data.message];
        } else {
            slugErrorMessages.value = ['IDの検証中にエラーが発生しました。'];
        }
    }
    isSlugChecked.value = true;
};

</script>