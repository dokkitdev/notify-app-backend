<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-05-20
 * Time: 07:50
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PrivatePrebuild extends Model
{
    protected $table = 'new_private_prebuilds';
    protected $fillable = [
        'name',
        'qty',
        'ex_tax',
        'inc_tax',

    ];

    public function customer()
    {
        return $this->belongsTo(PrivateCustomer::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(PrivateCostCenter::class);
    }

}