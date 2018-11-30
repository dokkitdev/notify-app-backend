<?php

namespace App\Console\Commands;

use App\Service\JobUploader;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\ConsoleOutput;

class JobCommand extends Command
{
    protected $signature = 'upload:job';
    protected $description = 'Command upload all appointment job from sim pro';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        set_time_limit(0);
        (new JobUploader())->run();
    }
}