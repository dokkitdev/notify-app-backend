<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-02-03
 * Time: 17:21
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ReportRow extends Model
{
    protected $table = 'report_row';
    protected $fillable = [
        'job_id',
        'type',
        'site_name',
        'engineer',
        'part_no',
        'stock_name',
        'storage_location',
        'required',
        'assigned',
        'job_date',
    ];

    public function getNeeded()
    {
        return $this->required - $this->assigned;
    }
}
