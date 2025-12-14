<?php

namespace Tests\Unit;

use App\Services\TimeSlotService;
use Carbon\Carbon;
use Tests\TestCase;

class TimeSlotServiceTest extends TestCase
{
    /**
     * @test
     * 基本ケース: 休憩時間を含むシフトで、予約可能枠が正しく計算されることをテスト
     */
    public function it_calculates_available_slots_correctly_with_shift_including_breaks(): void
    {
        // 1. 準備 (Arrange)
        $service = new TimeSlotService();
        $targetDate = new Carbon('2025-12-08');
        $currentTime = new Carbon('2025-12-08 08:00:00');
        $shifts = [
            (object)['start' => '09:00', 'end' => '12:00'],
            (object)['start' => '13:00', 'end' => '18:00'],
        ];

        // 2. 実行 (Act)
        $result = $service->calculateAvailableTimeSlots(
            targetDate: $targetDate,
            bookingIntervalMinutes: 30,
            shifts: $shifts,
            existingBookings: [],
            totalDurationMinutes: 60,
            bookingDeadlineMinutes: 0, // このテストでは締め切りを考慮しない
            currentTime: $currentTime
        );

        // 3. 検証 (Assert)
        $expected = [
            '09:00',
            '09:30',
            '10:00',
            '10:30',
            '11:00', // 11:30は所要時間(60m)を足すと12:30になりシフト外なので除外
            '13:00',
            '13:30',
            '14:00',
            '14:30',
            '15:00',
            '15:30',
            '16:00',
            '16:30',
            '17:00', // 17:30は所要時間(60m)を足すと18:30になりシフト外なので除外
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * 既存予約があるケース: 予約済みの時間帯が正しく除外されることをテスト
     */
    public function it_excludes_slots_overlapping_with_existing_booking(): void
    {
        // 1. 準備 (Arrange)
        $service = new TimeSlotService();
        $targetDate = new Carbon('2025-12-08');
        $currentTime = new Carbon('2025-12-08 08:00:00');
        $shifts = [(object)['start' => '09:00', 'end' => '12:00']];
        $existingBookings = [(object)['start' => '10:00', 'end' => '11:00']];

        // 2. 実行 (Act)
        $result = $service->calculateAvailableTimeSlots(
            targetDate: $targetDate,
            bookingIntervalMinutes: 30,
            shifts: $shifts,
            existingBookings: $existingBookings,
            totalDurationMinutes: 30,
            bookingDeadlineMinutes: 0,
            currentTime: $currentTime
        );

        // 3. 検証 (Assert)
        // 既存予約(10:00-11:00)と所要時間(30m)を考慮し、
        // 10:00 (->10:30), 10:30 (->11:00) のスロットが除外される
        $expected = ['09:00', '09:30', '11:00', '11:30'];
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * 予約締め切り時間を考慮するケース: 締め切り時刻より前の枠が除外されることをテスト
     */
    public function it_excludes_slots_before_the_booking_deadline(): void
    {
        // 1. 準備 (Arrange)
        $service = new TimeSlotService();
        $targetDate = new Carbon('2025-12-08');
        $currentTime = new Carbon('2025-12-08 09:15:00'); // 現在 9:15
        $shifts = [(object)['start' => '09:00', 'end' => '12:00']];

        // 2. 実行 (Act)
        $result = $service->calculateAvailableTimeSlots(
            targetDate: $targetDate,
            bookingIntervalMinutes: 30,
            shifts: $shifts,
            existingBookings: [],
            totalDurationMinutes: 30,
            bookingDeadlineMinutes: 30, // 30分前まで予約可能
            currentTime: $currentTime
        );

        // 3. 検証 (Assert)
        // 現在 9:15 で 30分前まで予約可能なので、締め切りは 9:45。
        // 9:45 より後のスロットのみが返される。
        $expected = ['10:00', '10:30', '11:00', '11:30'];
        $this->assertEquals($expected, $result);
    }
}
