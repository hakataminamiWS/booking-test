export const useStaffApplicationStatus = () => {
    const getStatusText = (status: string): string => {
        switch (status) {
            case 'pending':
                return '承認待ち';
            case 'approved':
                return '承認済み';
            case 'rejected':
                return '却下';
            default:
                return status;
        }
    };

    const getStatusColor = (status: string): string => {
        switch (status) {
            case 'pending':
                return 'warning';
            case 'approved':
                return 'success';
            case 'rejected':
                return 'error';
            default:
                return 'grey';
        }
    };

    return {
        getStatusText,
        getStatusColor,
    };
};
