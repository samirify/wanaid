<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Core\Console\UpdateStatsCommand;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule
                ->command(UpdateStatsCommand::class, ['payments'])
                ->daily()
                ->environments(["development"])
                ->appendOutputTo("payments-stats-task.log");
        });
    }

    public function register() {}
}
