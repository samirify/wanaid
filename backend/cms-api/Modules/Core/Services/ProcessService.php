<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class ProcessService
{
    public function __construct() {}

    public function runBackgroundProcess($command, $data = [])
    {
        $phpBinaryFinder = new PhpExecutableFinder();

        $phpBinaryPath = $phpBinaryFinder->find();

        $process = new Process(array_merge([$phpBinaryPath, base_path('artisan'), $command], $data));

        $process->setoptions(['create_new_console' => true]);
        $process->start();
    }
}
