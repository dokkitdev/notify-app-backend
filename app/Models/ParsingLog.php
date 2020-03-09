<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-05-20
 * Time: 07:48
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ParsingLog extends Model
{
    const PRIVATE_TYPE = 'private';
    const HOUSING_TYPE = 'housing';
    const WAREHOUSE_TYPE = 'warehouse';
    const APPOINTMENTS_CHL_TYPE = 'Appointments/CHL';

    protected $table = 'parsing_logs';
    protected $fillable = [
        'type',
        'total_count',
        'total_success',
        'reasons',
        'ids',
        'parsing_date',
    ];

    protected $casts = [
        'reasons' => 'array',
        'ids' => 'array',
    ];

}
