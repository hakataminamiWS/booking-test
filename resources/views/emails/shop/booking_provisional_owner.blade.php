@php
    $shop = $booking->shop;
@endphp
オーナー 様

新しい仮予約が入りました。
承認・または却下の操作を行ってください。
--------------------------------------------------
店舗: {{ $shop->name }}
日時: {{ $booking->start_at->setTimezone($booking->timezone)->format('Y年m月d日 H:i') }} 〜 {{ $booking->end_at->setTimezone($booking->timezone)->format('H:i') }}
メニュー: {{ $booking->menu_name }}
金額: ¥{{ number_format($booking->menu_price) }}
担当者: {{ $booking->assigned_staff_name ?? '指名なし' }}
予約者: {{ $booking->booker_name }} 様
連絡先: {{ $booking->contact_email }} / {{ $booking->contact_phone }}
--------------------------------------------------

@if ($booking->note_from_booker)
[お客様からのメモ]
{{ $booking->note_from_booker }}

@endif
@if ($booking->provisionalBooking)
承認期限: {{ $booking->provisionalBooking->expires_at->setTimezone($booking->timezone)->format('Y年m月d日 H:i') }} まで
@endif

管理画面で確認してください。
