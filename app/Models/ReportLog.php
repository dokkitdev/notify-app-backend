<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportLog extends Model
{
    public const ZERO_REPORT_TYPE = 'Zero Report';
    public const ASSET_REPORT = 'Asset report';
    public const DAILY_REPORT = 'Daily Assets';

    protected $table = 'report_logs';
    protected $fillable = [
        'type',
        'filename',
    ];
}
