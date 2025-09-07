import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';

// このコンポーザブルは、予約フォームの状態とロジックを管理します。
export function useBookingForm(props: { shopId: string; action: string; }) {

    // 状態 (State)
    const step = ref(1);
    const form = reactive({
        date: null as Date | null,
        staff: null as { id: number; name: string } | null,
        service: null as { id: number; name: string, duration: number } | null,
        time: null as string | null,
    });

    const staffOptions = ref<{ id: number; name: string }[]>([]);
    const serviceOptions = ref<{ id: number; name: string, duration: number }[]>([]);
    const availableTimes = ref<string[]>([]);

    const loading = reactive({
        staff: false,
        service: false,
        availability: false,
    });
    // submitting はHTMLフォーム送信に切り替えたため不要

    // 算出プロパティ (Computed)
    const formattedDate = computed(() => {
        if (!form.date) return '';
        return form.date.toLocaleDateString('ja-JP', { year: 'numeric', month: 'long', day: 'numeric' });
    });

    const isNextDisabled = computed(() => {
        if (step.value === 1 && !form.date) return true;
        if (step.value === 2 && (!form.staff || !form.service || !form.time)) return true;
        return false;
    });

    // メソッド (Methods)
    const allowedDates = (date: Date) => {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return date >= today;
    };

    const fetchStaff = async () => {
        if (!form.date) return;
        loading.staff = true;
        try {
            console.log(`Fetching staff for shop ${props.shopId} on ${form.date.toISOString().split('T')[0]}`);
            const response = await axios.get(`/api/shops/${props.shopId}/staff`);
            staffOptions.value = response.data;
        } catch (e) {
            console.error(e);
        } finally {
            loading.staff = false;
        }
    };

    const fetchServices = async () => {
        loading.service = true;
        try {
            console.log(`Fetching services for shop ${props.shopId}`);
            const response = await axios.get(`/api/shops/${props.shopId}/menus`);
            serviceOptions.value = response.data;
        } catch (e) {
            console.error(e);
        } finally {
            loading.service = false;
        }
    };

    const fetchAvailability = async () => {
        if (!form.date || !form.staff || !form.service) return;
        loading.availability = true;
        availableTimes.value = [];
        try {
            console.log(`Fetching availability for...`, form);
            const response = await axios.get(`/api/shops/${props.shopId}/available-slots`, {
                params: {
                    date: form.date.toISOString().split('T')[0],
                    menu_id: form.service.id,
                    staff_id: form.staff?.id, // Pass staff_id if selected
                }
            });
            availableTimes.value = response.data.map((slot: string) => {
                return new Date(slot).toLocaleTimeString('ja-JP', { hour: '2-digit', minute: '2-digit' });
            });
        } catch (e) {
            console.error(e);
        } finally {
            loading.availability = false;
        }
    };

    const handleDateChange = (newDate: Date | null) => {
        form.date = newDate;
        if (!form.date) return;

        form.staff = null;
        form.service = null;
        form.time = null;
        availableTimes.value = [];

        fetchStaff();
        fetchServices();
        step.value = 2;
    };

    // Initial data fetch
    onMounted(() => {
        fetchServices(); // Fetch services on mount
    });

    // 公開する状態とメソッド
    return {
        step,
        form,
        staffOptions,
        serviceOptions,
        availableTimes,
        loading,
        formattedDate,
        isNextDisabled,
        allowedDates,
        handleDateChange,
        fetchAvailability,
    };
}
