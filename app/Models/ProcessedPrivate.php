<?php
/**
 * Created by PhpStorm.
 * User: applestock
 * Date: 2019-01-16
 * Time: 15:59
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProcessedPrivate extends Model
{
    protected $table = 'n_private_processed';
    protected $fillable = [
        'contract',
        'end_date',
        'type'
    ];
}