/**
 * 入力値を数値文字列に整形するヘルパー関数。
 * 全角数字の半角変換、非数字文字の除去、先頭の不要なゼロの除去を行う。
 *
 * @param value 入力値
 * @returns 整形後の文字列
 */
export function formatNumericInput(value: any): string {
    if (value === null || value === undefined) {
        return "";
    }
    const stringValue = String(value);
    const hankakuValue = stringValue.replace(/[０-９]/g, (s) =>
        String.fromCharCode(s.charCodeAt(0) - 0xfee0)
    );
    const onlyNumbers = hankakuValue.replace(/[^0-9]/g, "");
    if (onlyNumbers.length > 1 && onlyNumbers.startsWith("0")) {
        return parseInt(onlyNumbers, 10).toString();
    }
    return onlyNumbers;
}
