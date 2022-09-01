<?php

namespace App\Console\Commands;

use App\Models\ParsingConstant;
use App\Models\ReportLog;
use App\Service\simProRequestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class UploadZeroReport extends Command
{
    /** @var simProRequestService */
    protected $simpro;
    protected $signature = 'upload:zero:report {from} {to}';
    protected $description = 'Command description';

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
        $from = $this->argument('from');
        $to = $this->argument('to');
        $pages = $this->simpro->getRequestPage(
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
            $jobs = $this->simpro->getRequest('get', $page);
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
