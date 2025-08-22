import { ref } from "vue";

export function useBookingForm() {
    const name = ref("");
    const date = ref("");
    const isSubmitting = ref(false);
    const isSuccess = ref(false);
    const errorMessage = ref("");

    async function submitBooking() {
        isSubmitting.value = true;
        isSuccess.value = false;
        errorMessage.value = "";

        try {
            const res = await fetch("/api/bookings", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ name: name.value, date: date.value }),
            });

            if (!res.ok) {
                throw new Error("予約に失敗しました");
            }
            isSuccess.value = true;
        } catch (e) {
            errorMessage.value = e instanceof Error ? e.message : String(e);
        } finally {
            isSubmitting.value = false;
        }
    }

    return {
        name,
        date,
        isSubmitting,
        isSuccess,
        errorMessage,
        submitBooking,
    };
}
