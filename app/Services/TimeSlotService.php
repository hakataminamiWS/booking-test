<?php

namespace App\Services;

use Carbon\Carbon;
use DateTimeInterface;

class TimeSlotService
{
    /**
     * 予約可能な時間枠のリストを算出する（純粋関数）
     *
     * このメソッドは、渡されたパラメータのみに依存して予約可能な時間枠を計算します。
     * 内部でDBアクセスや現在時刻の取得などの副作用は発生しません。
     *
     * @param DateTimeInterface $targetDate 予約枠を計算する対象日
     * @param int $bookingIntervalMinutes 予約を受け付ける時間の間隔（分）
     * @param array $shifts スタッフの勤務時間範囲のリスト。各要素は 'start' と 'end' をプロパティに持つオブジェクト。例: [(object)['start' => '09:00', 'end' => '12:00'], (object)['start' => '13:00', 'end' => '18:00']]。シフトがない日は空配列。
     * @param array $existingBookings 既存予約のリスト。各要素は 'start' と 'end' をプロパティに持つオブジェクトの配列。例: [(object)['start' => '10:15', 'end' => '11:00']]
     * @param int $totalDurationMinutes 予約に必要な合計所要時間（分）
     * @param int $bookingDeadlineMinutes 「何分前まで予約可能か」を示す締め切り時間（分）
     * @param DateTimeInterface $currentTime 予約締め切りを判定するための基準となる現在時刻
     * @return array<string> 予約可能と判断された時間枠の文字列の配列。例: ['09:30', '10:00', ...]
     */
    public function calculateAvailableTimeSlots(
        DateTimeInterface $targetDate,
        int $bookingIntervalMinutes,
        array $shifts,
        array $existingBookings,
        int $totalDurationMinutes,
        int $bookingDeadlineMinutes,
        DateTimeInterface $currentTime
    ): array {
        // ステップ1: 1日分のタイムスロット候補を、予約枠の間隔に基づいて生成する。
        if ($bookingIntervalMinutes <= 0) {
            return [];
        }

        $timeSlots = [];
        $startOfDay = Carbon::instance($targetDate)->startOfDay();
        $endOfDay = Carbon::instance($targetDate)->endOfDay();

        for ($time = $startOfDay; $time->lte($endOfDay); $time->addMinutes($bookingIntervalMinutes)) {
            $timeSlots[] = $time->format('H:i');
        }

        // ステップ2: シフト配列が空の場合は、予約可能な時間枠はないため空配列を返す。
        if (empty($shifts)) {
            return [];
        }

        // ステップ3: 予約締め切り時刻を計算する。
        $deadline = Carbon::instance($currentTime)->addMinutes($bookingDeadlineMinutes);

        // ステップ4: 生成したタイムスロット候補をループし、以下の条件でフィルタリングする。
        //   - 条件A: 予約の開始時刻が、締め切り時刻を過ぎているか？
        //   - 条件B: 予約時間全体（開始時刻 + 所要時間）が、いずれかの勤務時間範囲内に完全に収まっているか？
        $availableSlots = array_filter($timeSlots, function ($slot) use ($targetDate, $deadline, $shifts, $totalDurationMinutes, $existingBookings) {
            $slotStartTime = Carbon::instance($targetDate)->setTimeFromTimeString($slot);

            // 条件A: 締め切り時刻チェック
            if (!$slotStartTime->isAfter($deadline)) {
                return false;
            }

            $slotEndTime = $slotStartTime->copy()->addMinutes($totalDurationMinutes);

            // 条件B: シフト範囲内チェック
            $isInShift = false;
            foreach ($shifts as $shift) {
                $shiftStartTime = Carbon::instance($targetDate)->setTimeFromTimeString($shift->start);
                $shiftEndTime = Carbon::instance($targetDate)->setTimeFromTimeString($shift->end);
                if ($slotStartTime->gte($shiftStartTime) && $slotEndTime->lte($shiftEndTime)) {
                    $isInShift = true;
                    break;
                }
            }
            if (!$isInShift) {
                return false; // どのシフトにも収まらない
            }

            // 条件C: 既存予約との重複チェック
            foreach ($existingBookings as $booking) {
                $bookingStartTime = Carbon::instance($targetDate)->setTimeFromTimeString($booking->start);
                $bookingEndTime = Carbon::instance($targetDate)->setTimeFromTimeString($booking->end);
                // 重複条件: (予約開始 < 既存終了) AND (予約終了 > 既存開始)
                if ($slotStartTime->lt($bookingEndTime) && $slotEndTime->gt($bookingStartTime)) {
                    return false; // 重複あり
                }
            }

            return true; // すべての条件をクリア
        });

        // ステップ5 & 6: すべての条件を満たしたタイムスロットを収集し、配列として返す。
        return array_values($availableSlots);
    }
    /**
     * 指定された時間枠がシフト内かどうかを判定する
     */
    public function isWithinShift(
        DateTimeInterface $start,
        DateTimeInterface $end,
        array $shifts
    ): bool {
        // シフト配列が空の場合はシフト外とみなす
        if (empty($shifts)) {
            return false;
        }

        foreach ($shifts as $shift) {
            // シフトの開始・終了時刻をDateTimeオブジェクトとして比較可能にする
            // $shift->start/end は 'H:i' 形式の文字列
            // $start/$end は日付情報も持っているため、時刻部分のみの比較あるいは日付を含めた比較が必要。
            // ここでは $start/$end の同一日付上の時刻として比較する。
            
            $shiftStart = Carbon::instance($start)->setTimeFromTimeString($shift->start);
            $shiftEnd = Carbon::instance($start)->setTimeFromTimeString($shift->end);

            // 完全に収まっているかチェック
            if (Carbon::instance($start)->gte($shiftStart) && Carbon::instance($end)->lte($shiftEnd)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 指定された時間枠が既存予約と競合していないかを判定する
     */
    public function hasConflict(
        DateTimeInterface $start,
        DateTimeInterface $end,
        array $existingBookings
    ): bool {
        foreach ($existingBookings as $booking) {
            $bookingStart = Carbon::instance($start)->setTimeFromTimeString($booking->start);
            $bookingEnd = Carbon::instance($start)->setTimeFromTimeString($booking->end);

            // 重複判定: (予約開始 < 既存終了) AND (予約終了 > 既存開始)
            if (Carbon::instance($start)->lt($bookingEnd) && Carbon::instance($end)->gt($bookingStart)) {
                return true;
            }
        }
        return false;
    }
}
