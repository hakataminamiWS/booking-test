import { ref, computed } from 'vue';
import type { Ref, Computed } from 'vue';

// --- Type Definitions (from Create.vue) ---
interface BusinessHour { weekday: number; start_time: string; end_time: string; is_closed: boolean; }
interface SpecialDay { date: string; start_time: string; end_time: string; }
interface Shop { id: number; name: string; slug: string; businessHoursRegular: BusinessHour[], specialOpenDays: SpecialDay[], specialClosedDays: SpecialDay[] }
interface StaffSchedule { weekday: number; start_time: string; end_time: string; }
interface Staff { id: number; profile: { nickname: string; }; schedules: StaffSchedule[]; }
interface Booking { id: number; start_at: string; end_at: string; assigned_staff_id: number; }

/**
 * Helper function to convert "HH:MM" or "HH:MM:SS" string to minutes from midnight.
 */
const timeToMinutes = (timeStr: string): number => {
    const [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + minutes;
};

/**
 * Helper function to check if two time ranges overlap.
 */
const isOverlap = (startA: number, endA: number, startB: number, endB: number): boolean => {
    return startA < endB && startB < endA;
};

/**
 * Composable for calculating and managing booking availability.
 */
export function useBookingAvailability(
    shop: Computed<Shop>,
    bookings: Computed<Booking[]>,
    staffs: Computed<Staff[]>,
    duration: Computed<number>,
    assignedStaffId: Computed<number | null>
) {
    const selectedDate = ref<Date | null>(null);

    const setDate = (date: Date) => {
        selectedDate.value = date;
    };

    const availableTimeSlots = computed((): string[] => {
        if (!selectedDate.value || duration.value <= 0) {
            return [];
        }

        const date = selectedDate.value;
        const dateStr = `${date.getFullYear()}-${('0' + (date.getMonth() + 1)).slice(-2)}-${('0' + date.getDate()).slice(-2)}`;
        const dayOfWeek = date.getDay(); // Sunday: 0, Monday: 1, ...

        // 1. Determine shop's opening hours for the selected date
        const specialClosed = shop.value.specialClosedDays.find(d => d.date === dateStr);
        if (specialClosed) return []; // Closed all day

        const specialOpen = shop.value.specialOpenDays.find(d => d.date === dateStr);
        const regularHours = shop.value.businessHoursRegular.find(h => h.weekday === dayOfWeek);

        let shopOpenTime: number, shopCloseTime: number;

        if (specialOpen) {
            shopOpenTime = timeToMinutes(specialOpen.start_time);
            shopCloseTime = timeToMinutes(specialOpen.end_time);
        } else if (regularHours && !regularHours.is_closed) {
            shopOpenTime = timeToMinutes(regularHours.start_time);
            shopCloseTime = timeToMinutes(regularHours.end_time);
        } else {
            return []; // Closed on this regular day
        }

        // 2. Generate initial time slots (e.g., every 30 minutes)
        const allSlots: string[] = [];
        for (let t = shopOpenTime; t < shopCloseTime; t += 30) {
            const hours = Math.floor(t / 60);
            const minutes = t % 60;
            allSlots.push(`${('0' + hours).slice(-2)}:${('0' + minutes).slice(-2)}`);
        }

        // 3. Filter slots based on availability
        const availableSlots = allSlots.filter(slot => {
            const slotStart = timeToMinutes(slot);
            const slotEnd = slotStart + duration.value;

            // Slot must end within business hours
            if (slotEnd > shopCloseTime) return false;

            // Check against today's current time
            const now = new Date();
            if (date.toDateString() === now.toDateString()) {
                const currentTimeInMinutes = now.getHours() * 60 + now.getMinutes();
                if(slotStart < currentTimeInMinutes) return false;
            }

            // 4. Check for staff availability and booking conflicts
            const relevantStaff = assignedStaffId.value ? staffs.value.filter(s => s.id === assignedStaffId.value) : staffs.value;
            if (relevantStaff.length === 0) return false; // No staff can perform this service

            // Is there at least one staff member available for this slot?
            const isAnyStaffAvailable = relevantStaff.some(staff => {
                // a. Check staff's shift for the day
                const staffSchedule = staff.schedules.find(s => s.weekday === dayOfWeek);
                if (!staffSchedule) return false; // Not working on this day

                const shiftStart = timeToMinutes(staffSchedule.start_time);
                const shiftEnd = timeToMinutes(staffSchedule.end_time);
                if (slotStart < shiftStart || slotEnd > shiftEnd) return false; // Outside of shift

                // b. Check for overlapping bookings for this staff member
                const staffBookings = bookings.value.filter(b =>
                    b.start_at.startsWith(dateStr) && b.assigned_staff_id === staff.id
                );
                const hasConflict = staffBookings.some(booking => {
                    const bookingStart = timeToMinutes(booking.start_at.split(' ')[1]);
                    const bookingEnd = timeToMinutes(booking.end_at.split(' ')[1]);
                    return isOverlap(slotStart, slotEnd, bookingStart, bookingEnd);
                });
                if (hasConflict) return false;

                // If all checks pass for this staff member, they are available
                return true;
            });

            return isAnyStaffAvailable;
        });

        return availableSlots;
    });

    // Initialize with today's date
    setDate(new Date());

    return {
        selectedDate,
        setDate,
        availableTimeSlots,
    };
}