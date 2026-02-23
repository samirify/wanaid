<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Modules\Core\Services\PaymentService;

class UpdateStatsCommand extends Command
{
    public const STATS_CATEGORIES = ['payments', 'subscriptions'];
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'stats:update {category}';

    /**
     * The console command description.
     */
    protected $description = 'Update Statistics.';

    /**
     * Create a new command instance.
     */
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $category = $this->argument('category');

            $categories = explode('|', $category);

            logger()->info('Stats update started for category(s): ' . implode(',', $categories));

            sleep(1);

            foreach ($categories as $_category) {
                switch ($_category) {
                    case 'payments':
                        $this->paymentService->updatePaymentsStats();
                    default:
                        logger()->warning("Category {$_category} not supported!");
                }
            }

            logger()->info('Stats update completed successfully for category(s): ' . implode(',', $categories));
        } catch (\Throwable $th) {
            logger()->error($th->getMessage());
        }
    }
}
