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
        $requester = new Requester();
        $promise = $requester->getRequestAsync('companies/0/schedules/', [
            'Type' => 'job',
            'display' => 'all',
            'pageSize' => 1,
        ]);

        $promise->then(
            function (ResponseInterface $res) {
                $headers = $res->getHeaders();
                if (array_key_exists('Result-Total', $headers)) {
                    $pages = (int)ceil($headers['Result-Total'][0] / 25);
                    for ($i = $pages; $i > 0; $i--) {
                        dispatch(new AppointmentPage($i));
                    }
                }
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $requester->tick();
        $promise->wait();
    }
}