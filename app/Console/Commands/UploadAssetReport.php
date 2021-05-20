<?php

namespace App\Console\Commands;

use App\Models\AssetReport;
use App\Models\AssetReportValidation;
use App\Models\ParsingConstant;
use App\Service\simProRequestService;
use Illuminate\Console\Command;

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

//        if (!$assetConstant->is_need_parsing) {
//            return;
//        }
        $assetConstant->is_need_parsing = false;
        $assetConstant->save();

//        AssetReport::query()->truncate();
//        AssetReportValidation::query()->truncate();

        $ids = [
            31621,
            31622,
            31623,
            31624,
            31625,
            31631,
            31634,
            31635,
            31636,
            31637,
            31638,
            55245,
            55127,
            55126,
            55125,
            55124,
            55116,
            55115,
            55071,
            55070,
            55069,
            55068,
            55011,
            55010,
            55009,
            55008,
            55003,
            55002,
            55001,
            55000,
            54887,
            54886,
        ];

        $pages = $this->simpro->getRequestPage(
            'get',
            self::SITES_URL.'?Customers.ID=11514&columns=ID,CustomFields&ID=in('.implode(
                ',',
                $ids
            ).')'
        );
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

            $customFieldName = $customField->CustomField->Name ?? null;
            if ($customFieldName == 'Last Service Date' && $data['last_service_date'] === null) {
                $data['last_service_date'] = $value;
            } elseif (strpos($customFieldName, 'Fuel Type') !== false) {
                $data['fuel_type'] = $value;
            } elseif ($customFieldName == 'Type') {
                $data['type'] = $value;
            } elseif ($customFieldName == 'Make') {
                $data['make'] = $value;
            } elseif ($customFieldName == 'Model') {
                $data['model'] = $value;
            } elseif ($customFieldName == 'Last Years MOT Date') {
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

        AssetReport::query()
            ->where('site_id', $siteId)
            ->where('asset_id', $assetId)
            ->delete();

        AssetReportValidation::query()
            ->where('site_id', $siteId)
            ->where('asset_id', $assetId)
            ->delete();

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
