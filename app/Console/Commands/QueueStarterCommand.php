<?php

namespace App\Console\Commands;

use App\Appointment;
use App\Logs;
use App\Service\JobUploader;
use App\Service\simProRequestService;
use App\Service\TemplateGenerator;
use App\Templates;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class QueueStarterCommand extends Command
{
    protected $signature = 'queue:log:start';
    protected $description = 'Command run queue from log if need;';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $started_logs = Logs::where('is_started', '=', 1)
        ->where('is_finished', '=', 0)->get();
        if (count($started_logs) > 0) {
            echo '1 command is running!';
            return;
        }

        $log =  Logs::where('is_started', '=', 0)
            ->where('is_finished', '=', 0)
            ->where('command', '<>', null)
            ->orderBy('created_at', 'ASC')
            ->take(1)
            ->get()
            ->first();
        if ($log) {
            if ($log->command) {
                $log->is_started = 1;
                $log->save();
                exec($log->command);
            } else {
                $log->is_finished = 1;
                $log->save();
            }
        }
    }
}