<?php

namespace App\Console\Commands;

use App\Appointment;
use App\Logs;
use App\Models\Contract;
use App\Models\HousingJob;
use App\Models\PrivateCustomer;
use App\Service\JobUploader;
use App\Service\PrivateService;
use App\Service\simProRequestService;
use App\Service\TemplateGenerator;
use App\Templates;
use Illuminate\Console\Command;

class ProcessPrivateCommand extends Command
{
    protected $signature = 'process:private {customers} {log}';
    protected $description = 'Process pdfs';

    public function __construct()
    {
        parent::__construct();
    }

    public static function getCommand($customers, $log)
    {
        return 'php artisan process:private ' . implode(',', $customers) . ' ' . $log->id;
    }

    public function handle()
    {
        set_time_limit(0);
        $log = $this->argument('log');
        $log = Logs::find($log);
        $log->is_started = 1;
        $log->is_finished = 0;
        $log->save();
        $customers = $this->argument('customers');
        $customers = explode(',', $customers);
	dump($log);
        PrivateService::process($customers, $log);
        $log->is_finished = 1;
        $log->save();
    }
}
