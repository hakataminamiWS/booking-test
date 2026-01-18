export function useTimeFormatter() {
    /**
     * 分数を人間が読みやすい形式（日、時間、分）に変換します。
     * @param minutes 分数
     * @param suffix 末尾に付加する文字列（デフォルト: "前"）
     * @returns フォーマットされた文字列（例: "1日前", "2時間前", "30分前"）
     */
    const formatDeadline = (minutes: number | null | undefined, suffix = "前"): string => {
        if (minutes === null || minutes === undefined) return "";
        if (minutes === 0) return `0分${suffix}`;

        // 日単位（1440分 = 1日）
        if (minutes % 1440 === 0) {
            return `${minutes / 1440}日${suffix}`;
        }

        // 時間単位
        if (minutes % 60 === 0) {
            return `${minutes / 60}時間${suffix}`;
        }

        return `${minutes}分${suffix}`;
    };

    return {
        formatDeadline,
    };
}
