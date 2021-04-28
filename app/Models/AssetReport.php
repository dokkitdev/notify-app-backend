<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetReport extends Model
{
    protected $table = 'asset_reports';
    protected $fillable = [
        'site_id',
        'uprn',
        'asset_id',
        'asset_type',
        'type',
        'fuel_type',
        'make',
        'model',
        'last_service_date',
        'service_level_start_date',
        'job_due_date',
        'next_service_date',
        'job_stage',
        'service_level_name',
        'last_MOT_date',
        'service_due',
        'next_scheduled_appointment_date',
        'no_access_visits',
    ];
}
