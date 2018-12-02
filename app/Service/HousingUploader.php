<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/2/18
 * Time: 11:59 AM
 */

namespace App\Service;


use App\Jobs\HousingJob;
use App\Jobs\HousingPage;
use App\Jobs\HousingSite;
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
            function (ResponseInterface $res) {
                $jobs = json_decode($res->getBody()->getContents());
                if ($jobs) {
                    foreach ($jobs as $job_info) {
                        dispatch(new HousingJob($job_info));
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


    public function parseJob($job_info)
    {
        $promise = $this->requester->getRequestAsync('companies/0/jobs/' . $job_info->ID, [
            'display' => 'all',
        ]);
        $promise->then(
            function (ResponseInterface $res) {
                $job_info = json_decode($res->getBody()->getContents());
                if (isset($job_info->Site->ID) && count($job_info->Tags) > 0 && $job_info->DueDate) {
                    dispatch(new HousingSite($job_info));
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

    public function parseSite($job_info)
    {
        $promise = $this->requester->getRequestAsync('companies/0/sites/' . $job_info->Site->ID, [
        ]);
        $promise->then(
            function (ResponseInterface $res) use ($job_info) {
                $site_info = json_decode($res->getBody()->getContents());

                HousingJob::create([
                    'job_id' => $job_info->ID,
                    'company_name' => $job_info->Customer->CompanyName,
                    'due_date' => \DateTime::createFromFormat('Y-m-d', $job_info->DueDate),
                    'tags' => json_encode($job_info->Tags),
                    'stage' => $job_info->Stage,
                    'job_name' => $job_info->Sections[0]->CostCenters[0]->CostCenter->Name ?? null,
                    'site_id' => $site_info->ID,
                    'address' => $site_info->Address->Address ?? null,
                    'city' => $site_info->Address->City ?? null,
                    'state' => $site_info->Address->State ?? null,
                    'postal_code' => $site_info->Address->PostalCode ?? null,
                    'given_name' => $site_info->PrimaryContact->GivenName ?? null,
                    'family_name' => $site_info->PrimaryContact->FamilyName ?? null,

                ]);
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