<?php

namespace App\Console\Commands;

use App\Service\simProRequestService;
use Illuminate\Console\Command;

class SynkAssignedJobCostCenters extends Command
{
    private $simpro;
    protected $signature = 'synk:assigned';
    protected $description = 'Command update assigned cost center';

    public function __construct()
    {
        parent::__construct();
        $this->simpro = new simProRequestService();
    }

    public function handle()
    {
//        $begin_at = new \DateTime('+1 day');
//        $begin_at->setTime(0, 0, 0, 0);
//        $end_at = new \DateTime('+1 day');
//        $end_at->modify('+2 day');
//        $end_at->setTime(23, 59, 59);
//        $interval = \DateInterval::createFromDateString('1 day');
//        $period = new \DatePeriod($begin_at, $interval, $end_at);
//
//        foreach ($period as $dt) {
//            $this->runByDate($dt);
//        }

      $this->parseSchedule();
    }

//    public function runByDate($datetime)
//    {
//        $this->currentDate = $datetime;
//        $schedulesUrls = $this->simpro->getRequestPage(
//            'get',
//            '/api/v1.0/companies/0/schedules/?Type=job&Date='.$datetime->format(
//                'Y-m-d'
//            )
//        );
//
//        foreach ($schedulesUrls as $url) {
//            $this->getSchedulesByUrl($url);
//        }
//    }
//
//    public function getSchedulesByUrl($url)
//    {
//        $schedules = $this->simpro->getRequest('get', $url);
//        if ($schedules) {
//            foreach ($schedules as $schedule) {
//                $this->parseSchedule($schedule);
//            }
//        }
//    }
    public function parseSchedule()
    {
//        $job_id = array_first(explode('-', $schedule->Reference));
        $job_id = 130513;
        $job = $this->simpro->getRequest('get', '/api/v1.0/companies/0/jobs/'.$job_id.'?display=all');
        if (!$job) {
            return;
        }

        $stage = $job->Stage;
        if ($stage != 'Pending' && $stage != 'Progress') {
            return;
        }

        $sections = $job->Sections ?? null;
        if (!$sections || count($sections) < 1) {
            return;
        }
        foreach ($sections as $section) {
            $cost_centers = $section->CostCenters ?? null;
            if (!$cost_centers || !is_array($cost_centers)) {
                continue;
            }
            foreach ($cost_centers as $cost_center) {
                $this->parseCatalogsForJobSectionCostCenter($job, $section, $cost_center);
            }
        }
    }

    public function parseCatalogsForJobSectionCostCenter(
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
            $catalogsCount++;

            $storage_location = $this->simpro->getRequest(
                'get',
                '/api/v1.0/companies/0/catalogs/'.$catalog->Catalog->ID.'?columns=StorageLocation'
            );

            if ($storage_location) {
                $this->generateReportRow(
                    $job,
                    $catalog
                );
            }
        }
    }

    public function generateReportRow(
        $job,
        $catalog
    ) {
        dump($job->ID,  $catalog->Quantity->Assigned);
    }
}
