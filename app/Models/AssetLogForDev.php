<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetLogForDev extends Model
{
    protected $table = 'dev_logs';
    protected $fillable = ['desc'];

}
