<?php

namespace App\Jobs;

use App\Models\Logs;
use App\Models\ReportRow;
use App\Service\Sender\Sender;
use App\Service\Upload\ReportUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ReportOwnJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $timeout = 3600;
    protected $date;
    protected $log;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date, $log)
    {
        $this->date = $date;
        $this->log = $log;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = $this->date;
        $log = $this->log;
        $log = Logs::find($log);

        if ($this->attempts() > 3) {
            $log->delete();
            $this->delete();

            return;
        }
        $begin_at = new \DateTime('+1 day');
        $begin_at->setTime(0, 0, 0, 0);
        $end_at = new \DateTime('+1 day');
        if ($date == 3) {
            $end_at->modify('+2 day');
        }
        if ($date == 4) {
            $end_at->modify('+3 day');
        }
        $end_at->setTime(23, 59, 59);

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin_at, $interval, $end_at);


        $repotService = new ReportUpload();
        foreach ($period as $dt) {
            $repotService->runByDate($dt);
        }

        $path = __DIR__.'/../../public/images/logo.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/'.$type.';base64,'.base64_encode($data);

        $imageContent = file_get_contents($base64);
        $path = tempnam(sys_get_temp_dir(), 'prefix');

        file_put_contents($path, $imageContent);

        $html_folder = Config::get('constants.storage_html');
        $pdf_folder = Config::get('constants.storage_pdf');

        $log->customer_type = 'Project Warehouse report';
        foreach (['Project', 'Service'] as $type) {
            $generate = [];

            foreach ($period as $dt) {
                $weekday = $dt->format('l');
                $month = $dt->format('F');
                $year = $dt->format('Y');
                $day = ltrim($dt->format('d'), '0');
                if ($day % 10 == 1 && $day != 11) {
                    $day .= 'st';
                } else {
                    if ($day % 10 == 2 && $day != 12) {
                        $day .= 'nd';
                    } else {
                        if ($day % 10 == 3 && $day != 13) {
                            $day .= 'rd';
                        } else {
                            $day .= 'th';
                        }
                    }
                }

                $date = $weekday.', '.$day.' '.$month.' '.$year;
                $generate[$date] = [];
                $jobs = DB::table('report_row')
                    ->select('job_id', 'engineer')
                    ->groupBy('job_id')
                    ->groupBy('engineer')
                    ->where('type', $type)
                    ->where('job_date', '>=', $dt->format('Y-m-d 00:00:00'))
                    ->where('job_date', '<=', $dt->format('Y-m-d 23:59:59'))
                    ->get();


                foreach ($jobs as $job) {
                    $generate[$date][] = [
                        'job_id' => $job->job_id,
                        'rows' => ReportRow::where('job_id', '=', $job->job_id)->where(
                            'engineer',
                            '=',
                            $job->engineer
                        )->get(),
                    ];
                }
            }
            $content = View::make('admin.report.report', [
                'type' => $type,
                'generate' => $generate,
                'logo' => $path,
            ])->render();

            $date = new \DateTime();
            $unique_name = $type.'.report.'.$date->format('Y-m-d-H-i-s').'.html';
            file_put_contents($html_folder.'/'.$unique_name, $content);

            exec(
                Config::get(
                    'constants.libreoffice'
                ).' --headless --writer --convert-to pdf:writer_pdf_Export '.$html_folder.'/'.$unique_name.' --outdir '.$pdf_folder
            );

            $pdfName = str_replace('html', 'pdf', $unique_name);
            if ($type == 'Project') {
                $log->pdf = $pdfName;
                $log->is_finished = 1;
                $log->is_started = 0;
                $log->save();
            } else {
                $serviceLog = Logs::create(
                    [
                        'customer_type' => 'Service Warehouse report',
                        'pdf' => $pdfName,
                        'is_finished' => 1,
                        'is_started' => 0,
                    ]
                );
            }

            Sender::send(
                'warehouse@blueflameheat.co.uk',
                $type . ' Warehouse report',
                $type . ' Warehouse report',
                null,
                $pdf_folder.'/'.$pdfName,
                $pdfName
            );
        }
    }
}
