<?php

namespace App\Jobs;

use App\Models\ParsingConstant;
use App\Models\ReportLog;
use App\Service\simProRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Config;

class ZeroReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $from;
    private $to;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $from = $this->from;
        $to = $this->to;
        $simpro = new simProRequestService();
        $pages = $simpro->getRequestPage(
            'get',
            '/api/v1.0/companies/0/jobs/?Customer.ID=11514&columns=ID,OrderNo,DateIssued,Stage,Status,Total&DateIssued=between('.$from.','.$to.')'
        );
        $name = 'CHL_ZeroValueJobs_'.Date('YmdHi').'.csv';
        $pdfFolder = Config::get('constants.reports');
        $path = $pdfFolder.'/'.$name;
        $fp = fopen($path, 'w');
        $titles =
            [
                'Work Order',
                'simPRO ID',
                'Date Issued',
                'Stage',
                'Status',
                'Total',
            ];
        fputcsv($fp, $titles, ',');

        foreach ($pages as $page) {
            $jobs = $simpro->getRequest('get', $page);
            foreach ($jobs as $job) {
                $exTax = $job->Total->ExTax;
                if ($exTax != 0) {
                    continue;
                }
                $stage = $job->Stage;
                $data = [
                    $job->OrderNo,
                    $job->ID,
                    $job->DateIssued,
                    $stage == 'Archived' ? '' : $stage,
                    $job->Status->Name,
                    $exTax,
                ];
                fputcsv($fp, $data, ',');
            }
        }
        fclose($fp);
        ReportLog::create(
            [
                'type' => ReportLog::ZERO_REPORT_TYPE,
                'filename' => $name,
            ]
        );

        $zeroConstant = ParsingConstant::query()
            ->where('type', ParsingConstant::ZERO_TYPE)
            ->first();
        if ($zeroConstant) {
            $zeroConstant->is_need_parsing = false;
            $zeroConstant->save();
        }
    }
}
