<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-02-02
 * Time: 18:33
 */

namespace App\Service\Upload;


use App\Models\ParsingLog;
use App\Models\ReportRow;
use App\Service\simProRequestService;

class ReportUpload
{
    /** @var simProRequestService */
    private $simpro;
    private $totalCount = 0;
    private $totalSuccess = 0;
    private $reasons = [];
    private $ids = [];

    public function __construct()
    {
        $this->simpro = new simProRequestService();
    }


    public function run()
    {
        $todayDate = new \DateTime('-2 day');
//        $todayDate = new \DateTime('+1 day');
        $this->currentDate = new \DateTime();
        $schedulesUrls = $this->simpro->getRequestPage(
            'get',
            '/api/v1.0/companies/0/schedules/?Type=job&Date='.$todayDate->format(
                'Y-m-d'
            )
        );
        $this->totalCount = $this->simpro->result_count;
        $this->totalSuccess = 0;
        $this->reasons = [];
        $this->ids = [];
        foreach ($schedulesUrls as $url) {
            $this->getSchedulesByUrl($url);
        }
        ParsingLog::create(
            [
                'type' => ParsingLog::WAREHOUSE_TYPE,
                'total_count' => $this->totalCount,
                'total_success' => $this->totalSuccess,
                'reasons' => $this->reasons,
                'ids' => $this->ids,
                'parsing_date' => $todayDate->format('Y-m-d'),
            ]
        );
    }

    protected $currentDate;

    public function runByDate($datetime)
    {
        $this->currentDate = $datetime;
        dump('/api/v1.0/companies/0/schedules/?Type=job&Date='.$datetime->format('Y-m-d'));
        $schedulesUrls = $this->simpro->getRequestPage(
            'get',
            '/api/v1.0/companies/0/schedules/?Type=job&Date='.$datetime->format(
                'Y-m-d'
            )
        );
        $this->totalCount = $this->simpro->result_count;
        $this->totalSuccess = 0;
        $this->reasons = [];
        $this->ids = [];
        foreach ($schedulesUrls as $url) {
            $this->getSchedulesByUrl($url);
        }
        ParsingLog::create(
            [
                'type' => ParsingLog::WAREHOUSE_TYPE,
                'total_count' => $this->totalCount,
                'total_success' => $this->totalSuccess,
                'reasons' => $this->reasons,
                'ids' => $this->ids,
                'parsing_date' => $datetime->format('Y-m-d'),
            ]
        );
    }

    public function getSchedulesByUrl($url)
    {
        $schedules = $this->simpro->getRequest('get', $url);
        if ($schedules) {
            foreach ($schedules as $schedule) {
                $this->parseSchedule($schedule);
            }
        }
    }

    public function parseSchedule($schedule)
    {
        $job_id = array_first(explode('-', $schedule->Reference));
        dump('job_id ' . $job_id);
//        if ($job_id != 103429) {
//            return;
//        }
//        dump('!!103429!!!');
        $job = $this->simpro->getRequest('get', '/api/v1.0/companies/0/jobs/'.$job_id.'?display=all');
        if (!$job) {
            return;
        }
        $this->ids[] = $job_id;

        $stage = $job->Stage;
        dump($stage);
        if ($stage != 'Pending' && $stage != 'Progress') {
            $this->reasons[] = 'Job "'.$job_id.'". Stage is not Pending or Progress';

            return;
        }

        $sections = $job->Sections ?? null;
        if (!$sections || count($sections) < 1) {
            $this->reasons[] = 'Job "'.$job_id.'" has not Sections';

            return;
        }
        $this->totalSuccess++;
        foreach ($sections as $section) {
            $cost_centers = $section->CostCenters ?? null;
            if (!$cost_centers || !is_array($cost_centers)) {
                continue;
            }
            foreach ($cost_centers as $cost_center) {
                dump('cost center - '.$cost_center->ID);
                if ($cost_center->Total->ExTax == 0) {
                    dump('cost center - '.$cost_center->ID.' ex tax zero');
                    continue;
                }
                $this->parseCatalogsForJobSectionCostCenter($schedule, $job, $section, $cost_center);
            }
        }
//        die;
    }

    public function parseCatalogsForJobSectionCostCenter(
        $schedule,
        $job,
        $section,
        $cost_center
    ) {
        $catalogs = $this->simpro->getRequest(
            'get',
            '/api/v1.0/companies/0/jobs/'.$job->ID.'/sections/'.$section->ID.'/costCenters/'.$cost_center->ID.'/stock/'
        );
        $catalogsCount = 0;
        foreach ($catalogs as $catalog) {
            if ($catalog->Quantity->Required <= $catalog->Quantity->Assigned) {
                dump('Catalog id '.$catalog->Catalog->ID.' less then assigned');
                    continue;
            }
            $catalogsCount++;

            $storage_location = $this->simpro->getRequest(
                'get',
                '/api/v1.0/companies/0/catalogs/'.$catalog->Catalog->ID.'?columns=StorageLocation'
            );
            if ($storage_location) {
                $this->generateReportRow(
                    $schedule,
                    $job,
//                    $section,
//                    $cost_center,
                    $catalog,
                    $storage_location
                );
            }
        }
        dump('total catalogs ' . $catalogsCount . ' total catalogs without conditions ' . count($catalogs));
    }

    public function generateReportRow(
        $schedule,
        $job,
        $catalog,
        $storage_location
    ) {
        $reportRow = ReportRow::where('part_no', $catalog->Catalog->PartNo)
            ->where('site_name', $job->Site->Name)
            ->where('engineer', $schedule->Staff->Name)
            ->where('job_id', $job->ID)
            ->get();

        if (count($reportRow) < 1) {
            ReportRow::create([
                                  'job_id' => $job->ID,
                                  'site_name' => $job->Site->Name,
                                  'engineer' => $schedule->Staff->Name,
                                  'part_no' => $catalog->Catalog->PartNo,
                                  'stock_name' => $catalog->Catalog->Name,
                                  'storage_location' => $storage_location->StorageLocation,
                                  'required' => $catalog->Quantity->Required,
                                  'assigned' => $catalog->Quantity->Assigned,
                                  'job_date' => $this->currentDate->format('Y-m-d H:i:s'),
                              ]);
        }
    }


}
