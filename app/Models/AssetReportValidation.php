<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetReportValidation extends Model
{
    protected $table = 'asset_report_validations';
    protected $fillable = [
        'site_id',
        'uprn',
        'fuel_type',
        'asset_type',
        'asset_id',
        'asset_report_id',
        'error',
        'service_level_name',
        'job_stage',
    ];
}
