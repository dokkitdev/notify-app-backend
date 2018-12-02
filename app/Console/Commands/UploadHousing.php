<?php

namespace App\Console\Commands;

use App\Jobs\HousingPage;
use App\Service\HousingUploader;
use App\Service\Requester;
use App\Service\simProRequestService;
use App\Service\simProService;
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
        (new HousingUploader())
            ->startParsing();
    }

//    public function a($i)
//    {
//        $requester = new Requester();
//        $promise = $requester->getRequestAsync('companies/0/jobs/', [
//            'display' => 'all',
//            'pageSize' => 25,
//            'page' => $i
//        ]);
//
//        $promise->then(
//            function (ResponseInterface $res) {
//                $jobs = json_decode($res->getBody()->getContents());
//                if ($jobs) {
//                    foreach ($jobs as $job) {
//                        dump('b');
//                        $this->b($job);
//                    }
//                }
//            },
//            function (RequestException $e) {
//                echo $e->getMessage() . "\n";
//                echo $e->getRequest()->getMethod();
//            }
//        );
//        $requester->tick();
//        $promise->wait();
//    }
//
//    public function b($job)
//    {
//        $requester = new Requester();
//        $promise = $requester->getRequestAsync('companies/0/jobs/' . $job->ID, [
//            'display' => 'all',
//        ]);
//        $promise->then(
//            function (ResponseInterface $res) {
//                $job_info = json_decode($res->getBody()->getContents());
//                if (isset($job_info->Site->ID) && count($job_info->Tags) > 0 && $job_info->DueDate) {
//                    dump($job_info->Customer);
//                    dump($job_info->ID);
//                    dump($job_info->DueDate);
//                    dump($job_info->Tags);
//                    dump($job_info->Stage);
//                    $this->c($job_info->Site->ID);
//                    die;
//                }
//            },
//            function (RequestException $e) {
//                echo $e->getMessage() . "\n";
//                echo $e->getRequest()->getMethod();
//            }
//        );
//        $requester->tick();
//        $promise->wait();
//    }
//
//    public function c($site)
//    {
//        $requester = new Requester();
//        $promise = $requester->getRequestAsync('companies/0/sites/' . $site, [
//        ]);
//        $promise->then(
//            function (ResponseInterface $res) {
//                $site_info = json_decode($res->getBody()->getContents());
//                dump($site_info->Address);
//                dump($site_info->PrimaryContact);
//                die;
//            },
//            function (RequestException $e) {
//                echo $e->getMessage() . "\n";
//                echo $e->getRequest()->getMethod();
//            }
//        );
//        $requester->tick();
//        $promise->wait();
//        die;
//    }
}
