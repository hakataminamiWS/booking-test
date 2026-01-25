@php
    $shop = $booking->shop;
@endphp
{{ $booking->booker_name }} 様

以下の通り予約内容が変更されました。
--------------------------------------------------
店舗: {{ $shop->name }}
日時: {{ $booking->start_at->setTimezone($booking->timezone)->format('Y年m月d日 H:i') }} 〜 {{ $booking->end_at->setTimezone($booking->timezone)->format('H:i') }}
メニュー: {{ $booking->menu_name }}
金額: ¥{{ number_format($booking->menu_price) }}
担当者: {{ $booking->assigned_staff_name ?? '指名なし' }}
--------------------------------------------------

@if ($booking->note_from_booker)
[お客様からのメモ]
{{ $booking->note_from_booker }}

@endif

▼ 予約キャンセルの期日 ▼
キャンセル期限: {{ $deadline->format('Y年m月d日 H:i') }} まで

▼ キャンセルはこちらから ▼
{{ $cancelUrl }}

ご来店を心よりお待ちしております。
