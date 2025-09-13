<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shop;

class ShopPruneDeleted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:prune-deleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '論理削除されてから30日経過した店舗を物理削除する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('物理削除対象の店舗を検索中...');

        $deletedShops = Shop::where('status', 'deleting')
                            ->where('updated_at', '<=', now()->subDays(30))
                            ->get();

        if ($deletedShops->isEmpty()) {
            $this->info('物理削除対象の店舗は見つかりませんでした。');
            return Command::SUCCESS;
        }

        foreach ($deletedShops as $shop) {
            $shop->delete(); // 物理削除
            $this->info('店舗ID: ' . $shop->id . ' (' . $shop->name . ') を物理削除しました。');
        }

        $this->info('物理削除処理が完了しました。');

        return Command::SUCCESS;
    }
}
