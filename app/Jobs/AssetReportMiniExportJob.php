<?php

namespace App\Jobs;

use App\Models\AssetReportMini;
use App\Models\ReportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class AssetReportMiniExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '2048M');

        $assetReports = AssetReportMini::query()
            ->where('created_at', '>=', new \DateTime('-1 day'))
            ->get();

        AssetReportMini::query()->truncate();

        $pdfFolder = Config::get('constants.reports');
        $name = 'CHL_InstalledEquipment_Daily_'.Date('YmdHi').'.csv';
        $fp = fopen($pdfFolder.'/'.$name, 'w');
        fputcsv(
            $fp,
            [
                'Site ID',
                '~UPRN',
                'AssetID',
                'AssetType',
                'Type',
                'Fuel Type',
                'Make',
                'Model',
                'Last Service Date',
                'Service Level Start Date',
                'Job Due Date',
                'Service Level Next Service Date',
                'Job Stage',
                'Service Level Name',
                'Last MOT Date',
                'Service Due',
                'Next Scheduled Appointment Date',
                'No Access Visits',
            ],
            ','
        );
        $now = Date('Y-m-d');
        $today = (new \DateTime('+1 day'))->format('Y-m-d');
        foreach ($assetReports as $assetReport) {
            $jobDueDate = '';
            $jobStage = $assetReport->job_stage;
            if (in_array($jobStage, ['Progress', 'Pending'])) {
                $jobDueDate = $assetReport->job_due_date;
            }
            $d = null;
            if ($assetReport->next_scheduled_appointment_date) {
                list($d) = explode(' ', $assetReport->next_scheduled_appointment_date);
            }

            if (!$jobDueDate || ($jobDueDate < $today)) {
                $serviceDue = $assetReport->next_service_date;
            } else {
                $serviceDue = $jobDueDate;
            }


            fputcsv(
                $fp,
                [
                    $assetReport->site_id,
                    $assetReport->uprn,
                    $assetReport->asset_id,
                    $assetReport->asset_type,
                    $assetReport->type,
                    $assetReport->fuel_type,
                    $assetReport->make,
                    $assetReport->model,
                    $assetReport->last_service_date,
                    $assetReport->service_level_start_date,
                    $jobDueDate,
                    $assetReport->next_service_date,
                    $jobStage == 'Archived' ? '' : $jobStage,
                    $assetReport->service_level_name,
                    $assetReport->last_MOT_date,
                    $serviceDue,
                    $assetReport->next_scheduled_appointment_date && $d >= $now ? $assetReport->next_scheduled_appointment_date : '',
                    $assetReport->no_access_visits,
                ],
                ','
            );
        }
        fclose($fp);
        ReportLog::query()->create(
            [
                'type' => ReportLog::DAILY_REPORT,
                'filename' => $name,
            ]
        );
    }
}
