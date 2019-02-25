<?php

namespace App\Console\Commands;

use App\Jobs\HousingPage;
use App\Logs;
use App\Models\ReportRow;
use App\Service\HousingUploader;
use App\Service\Requester;
use App\Service\simProRequestService;
use App\Service\simProService;
use App\Service\TemplateGenerator;
use App\Service\Upload\HousingUpload;
use App\Service\Upload\ReportUpload;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Psr\Http\Message\ResponseInterface;
use Spipu\Html2Pdf\Html2Pdf;

class UploadReportOwn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:report:own {date} {log}';

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
        $date = $this->argument('date');
        $log = $this->argument('log');
        $log = Logs::find($log);
        $begin_at = new \DateTime();
        $begin_at->setTime(0, 0, 0, 0);
        $end_at = new \DateTime();
        if ($date == 3) {
            $end_at->modify('+2 day');
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
                ->select('job_id')
                ->groupBy('job_id')
                ->where('job_date', '>=', $dt->format('Y-m-d 00:00:00'))
                ->where('job_date', '<=', $dt->format('Y-m-d 23:59:59'))
                ->get();


            foreach ($jobs as $job) {
                $generate[$date][] = [
                    'job_id' => $job->job_id,
                    'rows' => ReportRow::where('job_id', '=', $job->job_id)->get()
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
        exec('libreoffice --headless --writer --convert-to pdf:writer_pdf_Export ' . $html_folder . '/' . $unique_name . ' --outdir ' . $pdf_folder);

        $log->pdf = str_replace('html', 'pdf', $unique_name);
        $log->is_finished = 1;
        $log->is_started = 0;
        $log->save();
//        unlink($html_folder . '/' . $unique_name);
    }

}
