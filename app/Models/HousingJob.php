<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/2/18
 * Time: 1:14 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class HousingJob extends Model
{
    protected $table = 'n_housing_job';
    protected $fillable = [
        'job_id',
        'company_name',
        'due_date',
        'tags',
        'stage',
        'job_name',
        'site_id',
        'address',
        'city',
        'state',
        'postal_code',
        'given_name',
        'family_name',

    ];
}