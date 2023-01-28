<?php

namespace App\Console\Commands;

use App\Models\AssetReport;
use App\Models\AssetReportMini;
use Illuminate\Console\Command;

class DeleteAssetReport extends Command
{
    protected $signature = 'delete:asset:report';
    protected $description = 'Command description';


    public function handle()
    {
        $assets = AssetReport::query()
            ->select('asset_id')
            ->where('is_updated', 0)
            ->get();
        $ids = $assets->map(function ($asset) {
            return $asset->asset_id;
        });


        AssetReport::query()
            ->select('asset_id')
            ->where('is_updated', 0)
            ->delete();
        if ($ids) {
            AssetReportMini::query()
                ->whereIn('asset_id', $ids)
                ->delete();
        }
    }
}

