<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-05-20
 * Time: 07:50
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PrivateAsset extends Model
{
    const PREBUILD_TYPE = 1;
    const CATALOG_TYPE = 2;
    const ONEOFF_TYPE = 3;


    protected $table = 'new_private_assets';
    protected $fillable = [
        'name',
        'qty',
        'ex_tax',
        'inc_tax',
        'type'
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