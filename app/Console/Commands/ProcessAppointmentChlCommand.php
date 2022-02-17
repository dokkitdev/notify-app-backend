<?php

namespace App\Console\Commands;

use App\Appointment;
use App\Logs;
use App\Models\Contract;
use App\Models\HousingJob;
use App\Models\PrivateCustomer;
use App\Service\AppointmentChlService;
use App\Service\JobUploader;
use App\Service\PrivateService;
use App\Service\simProRequestService;
use App\Service\TemplateGenerator;
use App\Templates;
use Illuminate\Console\Command;

class ProcessAppointmentChlCommand extends Command
{
    protected $signature = 'process:chl {appointments} {log}';
    protected $description = 'Process pdfs';

    public function __construct()
    {
        parent::__construct();
    }

    public static function getCommand($appointments, $log)
    {
        return 'php ' . base_path() . '/artisan process:chl ' . implode(',', $appointments) . ' ' . $log->id;
    }

    public function handle()
    {
        set_time_limit(0);
        $log = $this->argument('log');
        $log = Logs::find($log);
        $log->is_started = 1;
        $log->is_finished = 0;
        $log->save();
        $customers = $this->argument('appointments');
        $customers = explode(',', $customers);
        AppointmentChlService::startProcessing($customers, $log);
        $log->is_finished = 1;
        $log->save();
    }
}
