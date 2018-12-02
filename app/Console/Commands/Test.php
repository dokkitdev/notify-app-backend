<?php

namespace App\Console\Commands;

use App\Jobs\HousingPage;
use App\Models\HousingJob;
use App\Service\HousingUploader;
use App\Service\Requester;
use App\Service\simProRequestService;
use App\Service\simProService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:test';

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
        HousingJob::create([
            "job_id" => 354,
            "company_name" => "Coastline Housing Ltd",
            "due_date" => new \DateTime(),
            "tags" => "[{\"ID\":54,\"Name\":\"No Access 1 (Letter)\"}]",
            "stage" => "Progress",
            "job_name" => "Gas Planned Maintenance ",
            "site_id" => 32854,
            "address" => "15 Veor House",
            "city" => "Camborne",
            "state" => "Cornwall",
            "postal_code" => "TR14 8SS",
            "given_name" => "Price",
            "family_name" => "Price",
        ]);
        die;

    }

}
