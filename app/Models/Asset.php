<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/27/18
 * Time: 4:46 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $table = 'n_assets';
    protected $fillable = [
        'asset_id',
        'value',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}