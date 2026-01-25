export const useBookingStatus = () => {
    const getStatusText = (status: string) => {
        switch (status) {
            case 'confirmed': return '確定';
            case 'pending': return '保留';
            case 'cancelled': return 'キャンセル';
            case 'expired': return 'キャンセル（仮予約）';
            case 'completed': return '完了';
            default: return status;
        }
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'confirmed': return 'success';
            case 'pending': return 'warning';
            case 'cancelled': return 'error';
            case 'expired': return 'grey';
            // 'completed' not explicitly mapped in Dashboard.vue, using grey/secondary
            case 'completed': return 'secondary';
            default: return 'grey';
        }
    };

    return {
        getStatusText,
        getStatusColor,
    };
};
