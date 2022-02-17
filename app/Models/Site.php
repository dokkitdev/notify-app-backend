<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/27/18
 * Time: 4:46 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $table = 'n_sites';
    protected $fillable = [
        'site_id',
        'city',
        'address',
        'state',
        'postal_code',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}