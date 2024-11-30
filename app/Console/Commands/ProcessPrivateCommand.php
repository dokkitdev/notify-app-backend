<?php

namespace App\Console\Commands;

use App\Models\Logs;
use App\Service\PrivateService;
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
        return 'php ' . base_path() . '/artisan process:private ' . implode(',', $customers) . ' ' . $log->id;
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
        $isSuccess = PrivateService::process($customers, $log);
        dump($isSuccess);
        if ($isSuccess) {
            $log->is_finished = 1;
        } else {
            $log->is_started = 0;
        }
        $log->save();
    }
}
