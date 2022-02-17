<?php

namespace App\Console\Commands;

use App\Jobs\HousingPage;
use App\Service\HousingUploader;
use App\Service\Requester;
use App\Service\simProRequestService;
use App\Service\simProService;
use App\Service\Upload\HousingUpload;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

class UploadHousing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:housing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $yesterday = (new \DateTime('-1 day'))->format('Y-m-d');
        (new HousingUpload($yesterday))
            ->run();
    }
}
