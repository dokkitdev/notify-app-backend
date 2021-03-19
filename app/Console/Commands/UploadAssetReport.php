<?php

namespace App\Console\Commands;

use App\Jobs\HousingPage;
use App\Models\ParsingConstant;
use App\Models\AssetReport;
use App\Models\AssetReportValidation;
use App\Service\HousingUploader;
use App\Service\Requester;
use App\Service\simProRequestService;
use App\Service\simProService;
use App\Service\Upload\HousingUpload;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

class UploadAssetReport extends Command
{
    /** @var simProRequestService */
    protected $simpro;
    protected $signature = 'upload:asset:report';
    protected $description = 'Command description';

    const SITES_URL = '/api/v1.0/companies/0/sites/';

    public function __construct()
    {
        $this->simpro = new simProRequestService();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $assetConstant = ParsingConstant::firstOrCreate(
            [
                'type' => ParsingConstant::ASSET_TYPE,
            ],
            [
                'is_need_parsing' => false,
            ]
        );

        if (!$assetConstant->is_need_parsing) {
            return;
        }
        $assetConstant->is_need_parsing = false;
        $assetConstant->save();

        AssetReport::query()->truncate();
        AssetReportValidation::query()->truncate();
        $pages = $this->simpro->getRequestPage('get', self::SITES_URL.'?Customers.ID=11514&columns=ID,CustomFields');
        foreach ($pages as $page) {
            $sites = $this->simpro->getRequest('get', $page);
            foreach ($sites as $site) {
                $this->handleSite($site);
            }
        }
    }

    public function handleSite($site)
    {
        $siteId = $site->ID ?? 0;

        $assetsPages = $this->simpro->getRequestPage(
            'get',
            self::SITES_URL.$siteId.'/assets/?Archived=false&columns=ID,AssetType,CustomFields,LastTest,StartDate'
        );
        if (!$assetsPages) {
            return;
        }

        foreach ($assetsPages as $assetPage) {
            $assets = $this->simpro->getRequest('get', $assetPage);
            foreach ($assets as $asset) {
                $this->handleAsset($site, $asset);
            }
        }
    }

    public function handleAsset($site, $asset)
    {
        $today = strtotime(Date('Y-m-d'));
        $siteId = $site->ID ?? 0;
        $assetId = $asset->ID ?? 0;

        $errors = [];
        $data = [
            'site_id' => $siteId,
            'uprn' => null,
            'asset_id' => $assetId,
            'asset_type' => $asset->AssetType->Name ?? null,
            'type' => null,
            'fuel_type' => null,
            'make' => null,
            'model' => null,
            'last_service_date' => $asset->LastTest->Date ?? null,
            'service_level_start_date' => $asset->StartDate,
            'job_due_date' => null,
            'next_service_date' => null,
            'job_stage' => null,
            'service_level_name' => null,
            'last_MOT_date' => null,
            'service_due' => null,
            'next_scheduled_appointment_date' => null,
            'no_access_visits' => null,
        ];
        foreach ($site->CustomFields as $customField) {
            if ($customField->CustomField->ID == 4) {
                $data['uprn'] = $customField->Value;
                break;
            }
        }
        foreach ($asset->CustomFields as $customField) {
            $customFieldId = $customField->CustomField->ID ?? 0;
            $value = $customField->Value;
            if ($customFieldId == 1042) {
                $data['type'] = $value;
            } elseif ($customFieldId == 1531) {
                $data['fuel_type'] = $value;
            } elseif ($customFieldId == 1043) {
                $data['make'] = $value;
            } elseif ($customFieldId == 1044) {
                $data['model'] = $value;
            } elseif ($customFieldId == 1315 && $data['last_service_date'] === null) {
                $data['last_service_date'] = $value;
            } elseif ($customFieldId == 1943) {
                $data['last_MOT_date'] = $value;
            }
        }
        $data['last_service_date'] = $data['last_service_date'] ?: $asset->StartDate;


        $testHistories = $this->simpro->getRequest(
            'get',
            self::SITES_URL.$siteId.'/assets/'.$assetId.'/testHistory/?columns=Job'
        );

        if ($testHistories) {
            $job = $testHistories[0]->Job ?? new \stdClass();
            $data['job_due_date'] = $job->DueDate ?? null;

            $jobId = $job->ID ?? 0;
            $job = $this->simpro->getRequest('get', '/api/v1.0/companies/0/jobs/'.$jobId.'?columns=Stage,Tags,DueDate');
            if ($job) {
                $data['job_stage'] = $job->Stage ?? null;
                $data['service_due'] = $job->DueDate;
                foreach ($job->Tags ?? [] as $tag) {
                    $tagId = $tag->ID ?? 0;
                    if ($tagId == 56) {
                        $data['no_access_visits'] = 'No Access visits 1, 2, 3';
                    } elseif ($tagId == 55) {
                        $data['no_access_visits'] = 'No Access visits 1, 2';
                    } elseif ($tagId == 54) {
                        $data['no_access_visits'] = 'No Access visits 1';
                    }
                }
            }

            $schedules = $this->simpro->getRequest('get', '/api/v1.0/companies/0/schedules/?Reference='.$jobId.'-%');
            if ($schedules) {
                $schedule = $schedules[0] ?? new \stdClass();
                $scheduleDate = $schedule->Date;
                $blocks = $schedule->Blocks;
                if ($blocks) {
                    $block = $blocks[0];
                    $data['next_scheduled_appointment_date'] = $scheduleDate.' '.$block->StartTime.' - '.$block->EndTime;
                }
            }
        }

        $serviceLevels = $this->simpro->getRequest(
            'get',
            self::SITES_URL.$siteId.'/assets/'.$assetId.'/serviceLevels/'
        );

        if ($serviceLevels) {
            $serviceLevel = $serviceLevels[0];
            $data['next_service_date'] = $serviceLevel->ServiceDate ?? null;
            $data['service_level_name'] = $serviceLevel->ServiceLevel->Name ?? null;
            if ($data['service_due'] === null) {
                $data['service_due'] = $data['next_service_date'];
            }
        }
        if ($data['last_service_date']) {
            $daysBetween = $this->getDaysDiff($today, strtotime($data['last_service_date']));
            if ($daysBetween > 365) {
                $errors[] = 'Last service '.$daysBetween.' ago';
            }
        }

        if ($data['service_due']) {
            $daysBetween = $this->getDaysDiff($today, strtotime($data['service_due']));
            if ($daysBetween === 11) {
                $errors[] = 'Service due in 11 days';
            } elseif ($daysBetween === 1) {
                $errors[] = 'Service due tomorrow';
            }
        }

        if ($data['last_service_date'] && $data['service_due']) {
            $lastServiceDate = \DateTime::createFromFormat('Y-m-d', $data['last_service_date']);
            $serviceDue = \DateTime::createFromFormat('Y-m-d', $data['service_due']);
            $diff = $lastServiceDate->diff($serviceDue);
            $m = $diff->m;
            $d = $diff->d;
            if ($m > 12 || ($diff->m === 12 && $d > 0)) {
                $errors[] = 'Service complete outside of due date '.$m.' '.($m > 1 ? 'months' : 'month').' '.$d.' '.($d > 1 ? 'days' : 'day');
            }
        }

        if (!$data['uprn']) {
            $errors[] = 'No UPRN';
        }
        if (!$data['fuel_type']) {
            $errors[] = 'No Fuel Type found';
        }
        if (!$data['make']) {
            $errors[] = 'No Asset Make found';
        }
        if (!$data['model']) {
            $errors[] = 'No Model found';
        }

        $assetReport = AssetReport::create($data);
        foreach ($errors as $error) {
            AssetReportValidation::create(
                [
                    'site_id' => $data['site_id'],
                    'uprn' => $data['uprn'],
                    'fuel_type' => $data['fuel_type'],
                    'asset_type' => $data['asset_type'],
                    'asset_id' => $data['asset_id'],
                    'asset_report_id' => $assetReport->id,
                    'error' => $error,
                ]
            );
        }
    }


    public function getDaysDiff($firstTime, $secondTime)
    {
        $diff = $firstTime - $secondTime;

        return (int)abs(round($diff / (60 * 60 * 24)));
    }
}
