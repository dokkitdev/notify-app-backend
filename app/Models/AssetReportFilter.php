<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetReportFilter extends Model
{
    protected $table = 'asset_report_filters';
    protected $fillable = [
        'site_id',
        'service_levels',
        'asset_types',
        'errors',
        'stages',
    ];

    protected $casts = [
        'service_levels' => 'array',
        'asset_types' => 'array',
        'errors' => 'array',
        'stages' => 'array',
    ];
}
