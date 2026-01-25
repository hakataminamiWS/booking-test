@php
    $shop = $booking->shop;
@endphp
{{ $booking->booker_name }} 様

以下の予約をキャンセルしました。
--------------------------------------------------
店舗: {{ $shop->name }}
日時: {{ $booking->start_at->setTimezone($booking->timezone)->format('Y年m月d日 H:i') }} 〜 {{ $booking->end_at->setTimezone($booking->timezone)->format('H:i') }}
メニュー: {{ $booking->menu_name }}
担当者: {{ $booking->assigned_staff_name ?? '指名なし' }}
--------------------------------------------------

またのご来店を心よりお待ちしております。
