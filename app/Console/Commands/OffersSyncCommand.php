<?php

namespace App\Console\Commands;

use App\Models\Offer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OffersSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:sync 
                            {--stale-hours=24 : Mark offers as stale if not seen in X hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync offers: mark stale offers as out of stock';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $staleHours = (int) $this->option('stale-hours');
        $staleTime = now()->subHours($staleHours);

        $this->info("Checking for stale offers (not seen since {$staleTime})...");

        // Mark stale offers as out of stock
        $updated = Offer::where('in_stock', true)
            ->where('last_seen_at', '<', $staleTime)
            ->update([
                'in_stock' => false,
            ]);

        $this->info("Marked {$updated} offers as out of stock");

        // Delete very old offers (90+ days)
        $veryOldTime = now()->subDays(90);
        $deleted = Offer::where('last_seen_at', '<', $veryOldTime)->delete();

        if ($deleted > 0) {
            $this->info("Deleted {$deleted} very old offers (90+ days)");
        }

        Log::info('Offers sync completed', [
            'marked_out_of_stock' => $updated,
            'deleted' => $deleted,
        ]);

        return Command::SUCCESS;
    }
}
