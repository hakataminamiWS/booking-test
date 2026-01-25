@php
    $shop = $booking->shop;
    // Generate Cancellation Link
    // Note: In Mailable view, it's better to pass the URL from the Mailable class,
    // but for now we put logic here to match previous notification logic or move logic to Mailable.
    // Let's move logic to Mailable's build or pass it as data.
    // For this step, we will use the data passed from Mailable (booking).
    
    // We need to resolve the variable logic.
    // In Blade for text email:
@endphp
{{ $booking->booker_name }} 様

以下の内容で予約が確定しました。
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
@php
    $deadline = app(\App\Services\CancellationDeadlineService::class)
        ->getFormattedDeadline($shop, $booking->menu, $booking->start_at);
@endphp
キャンセル可能期限: {{ $deadline }} まで

▼ 予約内容のキャンセルはこちら ▼
{!! $cancelUrl !!}

ご来店を心よりお待ちしております。
