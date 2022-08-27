<?php

namespace App\Console\Commands;

use App\Models\AssetReport;
use App\Models\Job;
use Illuminate\Console\Command;

class DeleteAssetReport extends Command
{
    protected $signature = 'delete:asset:report';
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $size = Job::query()
            ->select('id')
            ->where('payload', 'LIKE', '%SiteJob%')
            ->orWhere('payload', 'LIKE', '%AssetJob%')
            ->first();
        if ($size) {
            return;
        }

        AssetReport::query()
            ->where('is_updated', 0)
            ->delete();
    }
}

