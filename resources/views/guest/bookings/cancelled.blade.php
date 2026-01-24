@extends('app')

@section('title', '予約キャンセル完了 - ' . $shop->name)

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">予約をキャンセルしました</h1>
                <p class="text-gray-600">
                    ご予約のキャンセルを承りました。<br>
                    またのご利用をお待ちしております。
                </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">キャンセル内容</h2>
                
                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">日時</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $booking->start_at->setTimezone($booking->timezone)->format('Y年m月d日 H:i') }} 〜 
                            {{ $booking->end_at->setTimezone($booking->timezone)->format('H:i') }}
                        </dd>
                    </div>

                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">メニュー</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $booking->menu_name }}
                        </dd>
                    </div>
                </dl>
            </div>
            
            <div class="text-center">
                <a href="{{ route('shop.entry', $shop->slug) }}" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    店舗トップへ戻る
                </a>
            </div>
        </div>
    </div>
@endsection
