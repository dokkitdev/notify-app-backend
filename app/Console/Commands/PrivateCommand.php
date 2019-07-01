<?php

namespace App\Console\Commands;

use App\Service\PrivateUploader;
use App\Service\simProRequestService;
use App\Service\Upload\PrivateUpload;
use Illuminate\Console\Command;

class PrivateCommand extends Command
{
    protected $signature = 'upload:private';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        (new PrivateUpload())
            ->run();
    }

}


