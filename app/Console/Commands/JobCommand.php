<?php

namespace App\Console\Commands;

use App\Service\Upload\JobUpload;
use Illuminate\Console\Command;

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
        (new JobUpload())
            ->uploadJob();
    }
}