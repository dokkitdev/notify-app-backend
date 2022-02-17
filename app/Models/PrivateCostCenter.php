<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-05-20
 * Time: 07:49
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PrivateCostCenter extends Model
{
    protected $table = 'new_private_cost_centers';
    protected $fillable = [
        'name',
        'section_id',
        'section_name',
        'ex_tax',
        'tax',
        'inc_tax',
    ];

    public function customer()
    {
        return $this->belongsTo(PrivateCustomer::class);
    }

    public function assets()
    {
        return $this->hasMany(PrivateAsset::class)->orderBy('type');
    }
}
