<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParsingConstant extends Model
{
    public const ASSET_TYPE = 'ASSET_TYPE';
    public const ZERO_TYPE = 'ZERO_TYPE';
    protected $table = 'parsing_constants';
    protected $fillable = [
        'type',
        'is_need_parsing',
    ];
}
