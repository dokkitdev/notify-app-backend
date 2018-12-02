<?php

namespace App\Jobs;

use App\Service\Requester;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Psr\Http\Message\ResponseInterface;

class AppointmentPage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $page;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($page)
    {
        $this->page = $page;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->attempts() > 5){ # ttl
            $this->delete();
        }

        $requester = new Requester();
        $promise = $requester->getRequestAsync('companies/0/schedules/', [
            'Type' => 'job',
            'display' => 'all',
            'pageSize' => 25,
            'page' => $this->page,
        ]);

        $promise->then(
            function (ResponseInterface $res) {
                $result = json_decode($res->getBody()->getContents());
                foreach ($result as $job) {
                    dispatch(new AppointmentJob($job));
                }
            },
            function (RequestException $e) {
                echo 'getPageWithJobs error here';
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $requester->tick();
        $promise->wait();
    }
}
