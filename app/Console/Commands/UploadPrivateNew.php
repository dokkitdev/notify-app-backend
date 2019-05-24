<?php

namespace App\Console\Commands;

use App\Jobs\HousingPage;
use App\Logs;
use App\Models\ReportRow;
use App\Service\HousingUploader;
use App\Service\Requester;
use App\Service\Sender\Sender;
use App\Service\simProRequestService;
use App\Service\simProService;
use App\Service\TemplateGenerator;
use App\Service\Upload\HousingUpload;
use App\Service\Upload\NewPrivateUpload;
use App\Service\Upload\ReportUpload;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Psr\Http\Message\ResponseInterface;
use Spipu\Html2Pdf\Html2Pdf;

class UploadPrivateNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:private:new';

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
        (new NewPrivateUpload())
            ->startToParse();
    }

}
