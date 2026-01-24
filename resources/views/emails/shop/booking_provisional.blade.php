@php
    $shop = $booking->shop;
@endphp
{{ $booking->booker_name }} 様

以下の内容で仮予約を受け付けました。
店舗からの確定連絡をお待ちください。
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
@if ($booking->provisionalBooking)
仮予約有効期限: {{ $booking->provisionalBooking->expires_at->setTimezone($booking->timezone)->format('Y年m月d日 H:i') }} まで

▼ 以下のリンクをクリックして予約を確定してください ▼
{!! $verifyUrl !!}

@endif

※この時点ではまだ予約は確定しておりません。
