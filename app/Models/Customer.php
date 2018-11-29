<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/27/18
 * Time: 4:46 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'n_customers';
    protected $fillable = [
        'first_name',
        'last_name',
        'company_id',
        'address',
        'city',
        'country',
        'postal_code',
        'given_name',
        'family_name',
    ];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}