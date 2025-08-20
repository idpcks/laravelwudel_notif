<?php

namespace LaravelWudel\LaravelWudelNotif\Console\Commands;

use Illuminate\Console\Command;
use LaravelWudel\LaravelWudelNotif\Models\PushSubscription;
use Illuminate\Support\Facades\DB;

class CleanupSubscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'push:cleanup-subscriptions 
                            {--days=30 : Remove subscriptions older than specified days}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old and expired push notification subscriptions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Cleaning up push notification subscriptions older than {$days} days...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No subscriptions will be deleted');
        }

        // Find old subscriptions
        $oldSubscriptions = PushSubscription::where('created_at', '<', now()->subDays($days))
            ->orWhere('last_used_at', '<', now()->subDays($days))
            ->get();

        if ($oldSubscriptions->isEmpty()) {
            $this->info('No old subscriptions found to clean up.');
            return 0;
        }

        $this->info("Found {$oldSubscriptions->count()} subscriptions to clean up.");

        if ($dryRun) {
            $this->displaySubscriptions($oldSubscriptions);
            return 0;
        }

        // Confirm deletion
        if (!$this->confirm("Are you sure you want to delete {$oldSubscriptions->count()} subscriptions?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Delete subscriptions
        $deletedCount = 0;
        $bar = $this->output->createProgressBar($oldSubscriptions->count());

        foreach ($oldSubscriptions as $subscription) {
            try {
                $subscription->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to delete subscription {$subscription->id}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Successfully deleted {$deletedCount} subscriptions.");

        // Show remaining subscriptions count
        $remainingCount = PushSubscription::count();
        $this->info("Remaining subscriptions: {$remainingCount}");

        return 0;
    }

    /**
     * Display subscriptions that would be deleted.
     */
    protected function displaySubscriptions($subscriptions): void
    {
        $this->newLine();
        $this->info('Subscriptions that would be deleted:');

        $data = [];
        foreach ($subscriptions as $subscription) {
            $data[] = [
                $subscription->id,
                $subscription->user_id,
                $subscription->endpoint,
                $subscription->created_at->format('Y-m-d H:i:s'),
                $subscription->last_used_at?->format('Y-m-d H:i:s') ?? 'Never',
            ];
        }

        $this->table(
            ['ID', 'User ID', 'Endpoint', 'Created', 'Last Used'],
            $data
        );
    }
}
