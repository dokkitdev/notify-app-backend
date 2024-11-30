<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/27/18
 * Time: 4:46 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    const TYPE_ELECTRIC = 'electric';
    const TYPE_GAS = 'gas';
    const TYPE_OTHER = 'other';
    const TABLE = 'cost_centers';
    protected $table = 'cost_centers';
    protected $fillable = [
        'name',
        'cost_center_id',
        'type',
    ];
    public $timestamps = false;
}
