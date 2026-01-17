export const useContractStatus = () => {
    // 契約申し込みのステータス
    const getAppStatusText = (status: string): string => {
        switch (status) {
            case 'pending':
                return '契約作成前';
            case 'approved':
                return '契約作成済み';
            case 'rejected':
                return '取り消し';
            default:
                return status;
        }
    };

    const getAppStatusColor = (status: string): string => {
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

    // 契約のステータス
    const getContractStatusText = (status: string): string => {
        switch (status) {
            case 'active':
                return '契約中';
            case 'expired':
                return '期限切れ';
            case 'cancelled':
                return '解約';
            default:
                return status;
        }
    };

    const getContractStatusColor = (status: string): string => {
        switch (status) {
            case 'active':
                return 'success';
            case 'expired':
                return 'error';
            case 'cancelled':
                return 'grey';
            default:
                return 'grey';
        }
    };

    return {
        getAppStatusText,
        getAppStatusColor,
        getContractStatusText,
        getContractStatusColor,
    };
};
