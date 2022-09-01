<?php

namespace App\Console\Commands;

use App\Models\Logs;
use App\Models\ReportRow;
use App\Service\Sender\Sender;
use App\Service\Upload\ReportUpload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class UploadReportOwnByDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:report:own:date {from} {to} {log}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        $begin_at = \DateTime::createFromFormat('Y-m-d',$this->argument('from'));
        $end_at = \DateTime::createFromFormat('Y-m-d',$this->argument('to'));
        $log = $this->argument('log');
        $log = Logs::find($log);
        if (!$begin_at || !$end_at) {
            dump('bad end!');
            return;
        }
        $end_at->setTime(23, 59, 59);

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin_at, $interval, $end_at);


        $repotService = new ReportUpload();
        foreach ($period as $dt) {
            $repotService->runByDate($dt);
        }
        $path = __DIR__ . '/../../../public/images/logo.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        $imageContent = file_get_contents($base64);
        $path = tempnam(sys_get_temp_dir(), 'prefix');

        file_put_contents($path, $imageContent);

        $html_folder = Config::get('constants.storage_html');
        $pdf_folder = Config::get('constants.storage_pdf');
        $generate = [];

        foreach ($period as $dt) {

            $weekday = $dt->format('l');
            $month = $dt->format('F');
            $year = $dt->format('Y');
            $day = ltrim($dt->format('d'), '0');
            if ($day % 10 == 1 && $day != 11) {
                $day .= 'st';
            } else if ($day % 10 == 2 && $day != 12) {
                $day .= 'nd';
            } else if ($day % 10 == 3 && $day != 13) {
                $day .= 'rd';
            } else {
                $day .= 'th';
            }

            $date = $weekday . ', ' . $day . ' ' . $month . ' ' . $year;
            $generate[$date] = [];
            $jobs = DB::table('report_row')
                ->select('job_id', 'engineer')
                ->groupBy('job_id')
                ->groupBy('engineer')
                ->where('job_date', '>=', $dt->format('Y-m-d 00:00:00'))
                ->where('job_date', '<=', $dt->format('Y-m-d 23:59:59'))
                ->get();


            foreach ($jobs as $job) {
                $generate[$date][] = [
                    'job_id' => $job->job_id,
                    'rows' => ReportRow::where('job_id', '=', $job->job_id)->where('engineer', '=', $job->engineer)->get()
                ];
            }
        }
        $content = View::make('admin.report.report', [
            'generate' => $generate,
            'logo' => $path,
        ])->render();

        $date = new \DateTime();
        $unique_name = 'report.' . $date->format('Y-m-d-H-i-s') . '.html';
        file_put_contents($html_folder . '/' . $unique_name, $content);
//        exec('/Applications/LibreOffice.app/Contents/MacOS/soffice --headless --writer --convert-to pdf:writer_pdf_Export ' . $html_folder . '/' . $unique_name . ' --outdir ' . $pdf_folder);
        exec(Config::get('constants.libreoffice') . ' --headless --writer --convert-to pdf:writer_pdf_Export ' . $html_folder . '/' . $unique_name . ' --outdir ' . $pdf_folder);

        $log->pdf = str_replace('html', 'pdf', $unique_name);
        $log->is_finished = 1;
        $log->is_started = 0;
        $log->save();

        Sender::send('warehouse@blueflameheat.co.uk', 'Warehouse report', 'Warehouser report', null, $pdf_folder . '/' . $log->pdf, $log->pdf);

//        unlink($html_folder . '/' . $unique_name);
    }

}
