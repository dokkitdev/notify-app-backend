<?php

namespace App\Console\Commands;

use App\Service\Upload\NewPrivateUpload;
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
        (new NewPrivateUpload())
            ->startToParse();
    }

}
