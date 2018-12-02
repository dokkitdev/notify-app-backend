<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/2/18
 * Time: 11:59 AM
 */

namespace App\Service;


use App\Jobs\HousingPage;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Psr\Http\Message\ResponseInterface;

class HousingUploader
{
    /** @var Requester */
    private $requester;

    public function __construct()
    {
        $this->requester = new  Requester();
    }

    public function startParsing()
    {
        $promise = $this->requester->getRequestAsync('companies/0/jobs/', [
            'display' => 'all',
            'pageSize' => 1,
        ]);

        $promise->then(
            function (ResponseInterface $res) {
                $headers = $res->getHeaders();
                if (array_key_exists('Result-Total', $headers)) {
                    $pages = (int)ceil($headers['Result-Total'][0] / 25);
                    for ($i = $pages; $i > 0; $i--) {
                        dispatch(new HousingPage($i));
                    }
                }
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $this->requester->tick();
        $promise->wait();
    }

    public function parseJobPage($page_num)
    {
        $promise = $this->requester->getRequestAsync('companies/0/jobs/', [
            'display' => 'all',
            'pageSize' => 25,
            'page' => $page_num
        ]);

        $promise->then(
            function (ResponseInterface $res) use ($page_num) {
                $jobs = json_decode($res->getBody()->getContents());
                DB::insert("INSERT INTO `temp`
(
`long`)
VALUES
('privetik $page_num')");

//                if ($jobs) {
//                    foreach ($jobs as $job) {
//                        dump('b');
//                        $this->b($job);
//                    }
//                }
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $this->requester->tick();
        $promise->wait();
    }


    public function parseJob($job_id)
    {
        $promise = $this->requester->getRequestAsync('companies/0/jobs/' . $job->ID, [
            'display' => 'all',
        ]);
        $promise->then(
            function (ResponseInterface $res) {
                $job_info = json_decode($res->getBody()->getContents());
                if (isset($job_info->Site->ID) && count($job_info->Tags) > 0 && $job_info->DueDate) {
                    dump($job_info->Customer);
                    dump($job_info->ID);
                    dump($job_info->DueDate);
                    dump($job_info->Tags);
                    dump($job_info->Stage);
                    $this->c($job_info->Site->ID);
                    die;
                }
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $this->requester->tick();
        $promise->wait();
    }

    public function parseSite($site_id)
    {
        $promise = $this->requester->getRequestAsync('companies/0/sites/' . $site, [
        ]);
        $promise->then(
            function (ResponseInterface $res) {
                $site_info = json_decode($res->getBody()->getContents());
                dump($site_info->Address);
                dump($site_info->PrimaryContact);
                die;
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
        $this->requester->tick();
        $promise->wait();
    }
}