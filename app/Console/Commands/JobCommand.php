<?php

namespace App\Console\Commands;

use App\Jobs\AppointmentPage;
use App\Service\JobUploader;
use App\Service\Requester;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Psr\Http\Message\ResponseInterface;
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
        (new JobUploader())
            ->run();
    }
}