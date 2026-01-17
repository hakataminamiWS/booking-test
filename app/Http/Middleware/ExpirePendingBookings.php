<?php

namespace App\Http\Middleware;

use App\Services\BookingExpirationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpirePendingBookings
{
    protected $expirationService;

    public function __construct(BookingExpirationService $expirationService)
    {
        $this->expirationService = $expirationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ルートパラメータから 'shop' を取得
        // 文字列 (slug) の場合と、モデル結合済み (Shop model) の場合がある
        $shop = $request->route('shop');

        if ($shop) {
            $shopId = null;

            if ($shop instanceof \App\Models\Shop) {
                $shopId = $shop->id;
            } elseif (is_string($shop)) {
                // slugの場合はID解決が必要だが、通常Webルートではモデル結合が解決された状態で来る
                // 仮に解決されていない場合や、shopパラメータがIDの場合はここで処理が必要だが
                // 今回のプロジェクト構成を見る限りモデル結合前提で進める。
                // 念のため Slug から ID を解決する簡易ロジックを入れるか、モデル結合依存にするか。
                // パフォーマンスを考慮し、モデル結合されている場合のみ実行する形を基本とする。
            }

            if ($shopId) {
                // 期限切れ処理を実行
                // エラーでページ表示を止めないよう try-catch する手もあるが、
                // トランザクション処理であり重大なエラーは起きにくいためそのまま実行
                $this->expirationService->expireOverduePendingBookings($shopId);
            }
        }

        return $next($request);
    }
}
