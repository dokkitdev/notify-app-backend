<?php

namespace App\Console\Commands;

use App\Models\Logs;
use App\Service\AppointmentChlService;
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
        dump('========startProcessing======');
        AppointmentChlService::startProcessing($customers, $log);
        $log->is_finished = 1;
        $log->save();
    }
}
