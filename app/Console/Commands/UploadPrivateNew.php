<?php

namespace App\Console\Commands;

use App\Jobs\PrivateParseJob;
use Illuminate\Console\Command;

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
        foreach (
            [
                new \DateTime('+17 day'),
                new \DateTime('+18 day'),
                new \DateTime('+19 day'),
                new \DateTime('+20 day'),
                new \DateTime('+21 day'),
                new \DateTime('+22 day'),
                new \DateTime('+23 day'),
                new \DateTime('+24 day'),
                new \DateTime('+25 day'),
                new \DateTime('+26 day'),
                new \DateTime('+27 day'),
                new \DateTime('+28 day'),
                new \DateTime('+29 day'),
                new \DateTime('+30 day'),

            ] as $nextRecurringDate
        ) {
            PrivateParseJob::dispatch($nextRecurringDate)->onQueue('high');
        }
    }

}
